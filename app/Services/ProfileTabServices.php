<?php

namespace App\Services;

use App\Models\{User, TicketSale, Order, UserMatch, SubscribedPlan, OrganizerProfile, LinkUpEvent, Payout, Transaction, GiftCoins, LiveStreamGumlet, LiveViewer, News};
use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\DB;

class ProfileTabServices
{
    // Fetch all tickets purchased by the user
    public function ticketTab(User $user)
    {
        $tickets = TicketSale::where('user_id', $user->id)
            ->with('checkins', 'event', 'cancellationRequest')
            ->get();

        return [
            'status' => true,
            'data' => $tickets
        ];
    }

    // Fetch all marketplace orders placed by the user
    public function marketPlaceTab(User $user)
    {
        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->get();

        return [
            'status' => true,
            'data' => $orders
        ];
    }

    // Get match statistics including likes sent, received, and mutual matches
    public function matchesTab(User $user)
    {
        $like_send = UserMatch::where('user_id', $user->id)
            ->where('status', 'like')
            ->count();

        $like_revived = UserMatch::where('target_user_id', $user->id)
            ->where('status', 'like')
            ->count();

        $likedUsers = UserMatch::where('user_id', $user->id)
            ->where('status', 'like')
            ->pluck('target_user_id');

        $likedByUsers = UserMatch::where('target_user_id', $user->id)
            ->where('status', 'like')
            ->pluck('user_id');

        $mutualUserIds = $likedUsers->intersect($likedByUsers);

        $mutualUsers = User::whereIn('id', $mutualUserIds)->get();

        return [
            'status' => true,
            'data' => [
                'like_send' => $like_send,
                'like_revived' => $like_revived,
                'mutual_Users' => $mutualUsers,
            ]
        ];
    }

    // Retrieve active subscription, remaining days, and subscription history
    public function subscriptionTab(User $user)
    {
        $active = SubscribedPlan::where('user_id', $user->id)
            ->where('status', 1)
            ->with('plan')
            ->latest('end_date')
            ->first();

        $daysLeft = $active ? max(0, now()->diffInDays($active->end_date, false)) : 0;

        $subscriptions = SubscribedPlan::where('user_id', $user->id)
            ->with('plan')
            ->get();

        return [
            'status' => true,
            'active' => $active,
            'days_left' => (int) $daysLeft,
            'subscriptions' => $subscriptions,
        ];
    }

    // Cancel an active subscription plan
    public function cancelSubscription(SubscribedPlan $plan)
    {
        $plan->status = 0;
        $plan->save();

        return true;
    }

    // Fetch coin payment history for the user
    public function LinkUpCoinTab(User $user)
    {
        $coins_history = Subscription::where('user_id', $user->id)
            ->where('type', 'coin')
            ->get();

        // Calculate coins earned (received from others)
        $coins_earned = GiftCoins::where('recieved_id', $user->id)
            ->sum('coins');

        // Calculate coins spent (sent to others)
        $coins_spent = GiftCoins::where('sender_id', $user->id)
            ->sum('coins');

        return [
            'status' => true,
            'coins_history' => $coins_history,
            'coins_earned' => $coins_earned,
            'coins_spent' => $coins_spent,
        ];
    }

    // Fetch organizer dashboard data including events, ticket sales, and payouts
    public function OrganizerTab(User $user)
    {
        $events_data = [];
        $payout_data = [];

        $profile = OrganizerProfile::where('user_id', $user->id)
            ->whereHas('kyc', function ($query) {
                $query->where('status', 'approved');
            })
            ->first();

        if ($profile) {
            $events_data = LinkUpEvent::where('organizer_id', $profile->id)
                ->withCount('tickets')
                ->withSum('ticketSales', 'fee')
                ->withSum('ticketSales', 'tax')
                ->withSum('ticketSales', 'total')
                ->addSelect([
                    'approved_refund_sum' => DB::table('ticket_sales')
                        ->join('cancellation_requests', 'cancellation_requests.ticket_id', '=', 'ticket_sales.id')
                        ->whereColumn('ticket_sales.link_up_event_id', 'link_up_events.id')
                        ->where('cancellation_requests.status', 'Approve + Refund')
                        ->selectRaw('COALESCE(SUM(cancellation_requests.refund_amount), 0)')
                ])
                ->get();

            $payout_data = Payout::where('organizer_id', $profile->id)
                ->where('status', 'verified')
                ->with('event')
                ->get();
        }

        return [
            'status' => true,
            'organizer_profile' => $profile ? true : false,
            'events_data' => $events_data,
            'payout_data' => $payout_data,
        ];
    }

    // Fetch user all transaction data
    public function walletTab(User $user)
    {
        $transection1 = Transaction::where('from_id', $user->id)
            ->where('status', 'success')
            ->orWhere('to_id', $user->id)
            ->get();

        $organizerId = OrganizerProfile::where('user_id', $user->id)
            ->whereHas('kyc', function ($query) {
                $query->where('status', 'approved');
            })
            ->first();

        $payouts = 0;
        if ($organizerId) {
            $payouts = Payout::where('organizer_id', $organizerId->id)
                ->where('status', 'verified')
                ->sum('net_amount');
        }

        // Transform transactions to consistent format
        $formattedTransactions = $transection1->map(function ($item) {
            return [
                'id' => $item->uuid,
                'type' => $item->processor_id,
                'amount' => $item->amount,
                'date' => $item->created_at,
            ];
        })->sortByDesc('date')->values();

        return [
            'status' => true,
            'transactions' => $formattedTransactions,
            'totalWallet' => $user?->balance('USD')->value->get(),
            'payouts' => $payouts,
        ];
    }

    //Fetch user all linkup live data
    public function linkUpLIve(User $user)
    {
        $liveStreams = LiveStreamGumlet::where('user_id', $user->id)
            ->withCount('viewers')
            ->addSelect([
                'total_watch_time' => LiveViewer::selectRaw('SUM(TIMESTAMPDIFF(SECOND, created_at, COALESCE(left_at, last_heartbeat, updated_at, created_at)))')
                    ->whereColumn('live_stream_gumlet_id', 'live_stream_gumlets.id')
            ])
            ->latest()
            ->get();

        // Load sum of coins from gifts
        $liveStreams->loadSum('gifts as total_coins', 'coins');

        // Fetch user's "live deposit" transactions to match with streams
        $userId = $user->id;
        $transactions = Transaction::where('to_id', $userId)
            ->where('processor_id', 'live deposit')
            ->get();

        $formattedData = $liveStreams->map(function ($stream) use ($transactions) {
            // Find transaction for this stream by matching stream_id in meta
            $transfer = $transactions->first(function ($tx) use ($stream) {
                $meta = $tx->meta;
                if (!is_array($meta)) {
                    $meta = json_decode($meta, true) ?? [];
                }
                $metaStreamId = $meta['stream_id'] ?? $meta['live_stream_id'] ?? null;
                return $metaStreamId && (string)$metaStreamId == (string)$stream->id;
            });

            $coins = (int)($stream->total_coins ?? 0);
            $revenue = $coins / 100;
            $watchTimeSeconds = (int)($stream->total_watch_time ?? 0);

            return [
                'id' => $stream->id,
                'title' => $stream->title,
                'watchers' => (int)$stream->viewers_count,
                'watch_time_mins' => max(0, round($watchTimeSeconds / 60, 2)),
                'likes' => (int)$stream->like_count,
                'coins' => $coins,
                'revenue' => (float)$revenue,
                'creator_share' => (float)$revenue * 0.5,
                'transfer_amount' => $transfer ? (float)$transfer->amount : 0,
                'platform_commission' => $transfer ? (float)($transfer->received ?? 0) : 0,
                'created_at' => $stream->created_at,
            ];
        });

        return [
            'status' => true,
            'data' => $formattedData
        ];
    }

    public function newsTab(User $user)
    {
        $news = News::where('user_id', $user->id)->get();

        return [
            'status' => true,
            'data' => $news
        ];
    }
}
