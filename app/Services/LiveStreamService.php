<?php

namespace App\Services;

use App\Models\LinkupLiveGift;
use App\Models\LiveComment;
use App\Models\LiveGift;
use App\Models\LiveStreamCategories;
use App\Models\LiveStreamGumlet;
use App\Models\LiveViewer;
use App\Models\PrivateLiveStreamSub;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use O21\LaravelWallet\Models\Custodian;

class LiveStreamService
{
    /**
     * Get active and upcoming streams formatted for frontend.
     */
    public function getStreamsForFrontend(): Collection
    {
        $user = auth()->user();
        $endedCutoff = now()->subMinutes(10);

        return LiveStreamGumlet::with('user')
            ->where(function ($query) use ($endedCutoff) {
                $query->whereIn('status', ['live', 'created'])
                    ->orWhere(function ($query) use ($endedCutoff) {
                        $query->where('status', 'completed')
                            ->whereNotNull('ended_at')
                            ->where('ended_at', '>=', $endedCutoff);
                    });
            })
            ->get()
            ->map(function ($s) use ($user) {
                $isSubscribed = false;

                if ($user) {
                    if ($s->user_id == $user->id) {
                        $isSubscribed = true;
                    } elseif ($s->visibility === 'private') {
                        $hasOneTime = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                            ->where('stream_id', $s->id)
                            ->where('status', 'active')
                            ->exists();

                        $monthly = PrivateLiveStreamSub::where('pay_user_id', $user->id)
                            ->where('user_streamer_id', $s->user_id)
                            ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
                            ->whereIn('status', ['active', 'Active'])
                            ->orderByDesc('created_at')
                            ->first();

                        $hasMonthly = false;
                        if ($monthly) {
                            $expiresAt = \Carbon\Carbon::parse($monthly->created_at)->addDays(30);
                            $hasMonthly = $expiresAt->isFuture();
                        }

                        if ($hasOneTime || $hasMonthly) {
                            $isSubscribed = true;
                        }
                    } else {
                        $isSubscribed = true; // Public streams are open
                    }
                }

                $isEnded = $s->status === 'completed';
                $viewerCount = $isEnded
                    ? LiveViewer::where('live_stream_gumlet_id', $s->id)->distinct()->count('user_id')
                    : LiveViewer::where('live_stream_gumlet_id', $s->id)->where('is_active', true)->count();

                return [
                    'id' => $s->id,
                    'public_id' => $s->public_id,
                    'user_id' => $s->user_id,
                    'title' => $s->title,
                    'host' => $s->user?->name ?? 'Host',
                    'hostAvatar' => $s->user?->avatar,
                    'category' => $s->broadcast_type ?? 'Just Chatting',
                    'status' => $isEnded ? 'ended' : ($s->status === 'live' ? 'live' : 'upcoming'),
                    'viewers' => $viewerCount,
                    'likes' => $s->like_count ?? 0,
                    'gifts' => (int) LiveGift::where('live_stream_gumlet_id', $s->id)->sum('qty'),
                    'cover' => $s->image_url,
                    'when' => $s->start_time ? $s->start_time->format('h:i A') : 'Upcoming',
                    'started_at' => $s->start_time?->toISOString(),
                    'ended_at' => $s->ended_at?->toISOString(),
                    'ended_expires_at' => $s->ended_at?->copy()->addMinutes(10)->toISOString(),
                    'duration_seconds' => $s->start_time && $s->ended_at
                        ? max(0, $s->start_time->diffInSeconds($s->ended_at))
                        : 0,
                    'products' => $s->products ?? [],
                    'visibility' => $s->visibility,
                    'subscription_rate' => (float) ($s->subscription_rate ?? 0),
                    'is_subscribed' => $isSubscribed,
                ];
            });
    }

    /**
     * Get all stream categories.
     */
    public function getCategories(): Collection
    {
        return LiveStreamCategories::query()
            ->orderBy('category')
            ->pluck('category')
            ->prepend('All');
    }

    /**
     * Create a new live stream record.
     */
    public function createStream(User $user, array $data): LiveStreamGumlet
    {
        $channelName = 'live_'.$user->id.'_'.Str::random(5);

        return LiveStreamGumlet::create([
            'user_id' => $user->id,
            'title' => $data['title'] ?? 'Untitled Stream',
            'broadcast_type' => $data['cat'] ?? 'Just Chatting',
            'location' => $data['loc'] ?? null,
            'visibility' => strtolower($data['visibility'] ?? 'public'),
            'base_resolution' => $data['base'] ?? '1920x1080',
            'output_resolution' => $data['out'] ?? '1280x720',
            'status' => 'live',
            'start_time' => now(),
            'stream_key' => $channelName,
            'stream_url' => $channelName,
            'resolution' => $data['out'] ?? '1280x720',
            'live_asset_id' => 'agora_'.Str::random(10),
            'live_video_source_id' => 'agora_source',
            'playback_url' => '',
            'products' => $data['products'] ?? [],
        ]);
    }

    /**
     * Get earnings data for a user.
     */
    public function getEarningsData(User $user): array
    {
        $myStreams = LiveStreamGumlet::where('user_id', $user->id)->get();

        $totalCoins = 0;
        foreach ($myStreams as $ms) {
            $totalCoins += LiveGift::where('live_stream_gumlet_id', $ms->id)->sum('coins');
        }

        $subscriptionGross = (float) PrivateLiveStreamSub::query()
            ->where('user_streamer_id', $user->id)
            ->sum('pay_amount');
        $transferred = (float) Transaction::query()
            ->where('to_id', $user->id)
            ->where('to_type', User::class)
            ->where('processor_id', 'live deposit')
            ->where('currency', 'USD')
            ->sum('amount');

        return [
            'collected' => round(($totalCoins * 0.01) + $subscriptionGross, 2),
            'gift_gross' => round($totalCoins * 0.01, 2),
            'subscription_gross' => round($subscriptionGross, 2),
            'transferred' => round($transferred, 2),
        ];
    }

    /**
     * Transfer live earnings to user's wallet.
     */
    public function transferEarnings(User $user, string $idempotencyKey): array
    {
        try {
            return DB::transaction(function () use ($user, $idempotencyKey) {
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

                $existingTransfer = Transaction::query()
                    ->where('uuid', $idempotencyKey)
                    ->where('to_id', $user->id)
                    ->where('to_type', User::class)
                    ->where('processor_id', 'live deposit')
                    ->first();

                if ($existingTransfer) {
                    return [
                        'success' => true,
                        'transferred' => (float) $existingTransfer->amount,
                        'new_balance' => $lockedUser->balance('USD')->value->get(),
                    ];
                }

                $streams = LiveStreamGumlet::query()
                    ->where('user_id', $user->id)
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();
                $streamIds = $streams->pluck('id');
                $giftCoins = LiveGift::query()
                    ->whereIn('live_stream_gumlet_id', $streamIds)
                    ->select('live_stream_gumlet_id', DB::raw('SUM(coins) as total_coins'))
                    ->groupBy('live_stream_gumlet_id')
                    ->pluck('total_coins', 'live_stream_gumlet_id');

                $availableGiftCoins = $streams->sum(fn (LiveStreamGumlet $stream) => max(
                    0,
                    (int) ($giftCoins[$stream->id] ?? 0) - (int) ($stream->paid_coins ?? 0)
                ));

                PrivateLiveStreamSub::query()
                    ->where('user_streamer_id', $user->id)
                    ->lockForUpdate()
                    ->get();
                $subscriptionGross = (float) PrivateLiveStreamSub::query()
                    ->where('user_streamer_id', $user->id)
                    ->sum('pay_amount');
                $subscriptionPaidGross = Transaction::query()
                    ->where('to_id', $user->id)
                    ->where('to_type', User::class)
                    ->where('processor_id', 'live deposit')
                    ->where('currency', 'USD')
                    ->lockForUpdate()
                    ->get()
                    ->sum(fn (Transaction $transaction) => (float) ($transaction->meta['subscription_gross_amount'] ?? 0));

                $availableGiftGross = round($availableGiftCoins * 0.01, 2);
                $availableSubscriptionGross = max(0, round($subscriptionGross - $subscriptionPaidGross, 2));
                $availableHostShare = round(($availableGiftGross + $availableSubscriptionGross) * 0.5, 2);

                if ($availableHostShare <= 0) {
                    return ['success' => false, 'message' => 'No eligible live earnings are available'];
                }

                $amount = $availableHostShare;
                $grossAmount = round($amount / 0.5, 2);
                $giftGrossToPay = min($availableGiftGross, $grossAmount);
                $subscriptionGrossToPay = round($grossAmount - $giftGrossToPay, 2);
                $remainingGiftCoins = (int) round($giftGrossToPay / 0.01);

                foreach ($streams as $stream) {
                    if ($remainingGiftCoins <= 0) {
                        break;
                    }
                    $total = (int) ($giftCoins[$stream->id] ?? 0);
                    $paid = (int) ($stream->paid_coins ?? 0);
                    $payHere = min(max(0, $total - $paid), $remainingGiftCoins);
                    if ($payHere <= 0) {
                        continue;
                    }
                    $stream->paid_coins = $paid + $payHere;
                    $stream->is_paid = $stream->paid_coins >= $total;
                    $stream->save();
                    $remainingGiftCoins -= $payHere;
                }

                $deposit = deposit($amount, 'USD')
                    ->from(Custodian::of('e_money'))
                    ->to($lockedUser)
                    ->overcharge()
                    ->processor('live deposit')
                    ->meta([
                        'type' => 'live_earnings_transfer',
                        'source' => 'live_dashboard',
                        'description' => 'Transferred from Live Dashboard (50/50 split)',
                        'note' => 'Live analytics earnings transfer',
                        'gross_amount' => $grossAmount,
                        'gift_gross_amount' => $giftGrossToPay,
                        'subscription_gross_amount' => $subscriptionGrossToPay,
                        'user_share' => $amount,
                        'platform_share' => round($grossAmount - $amount, 2),
                        'split' => '50/50',
                    ]);
                $deposit->model()->uuid = $idempotencyKey;
                $deposit->commit();

                return [
                    'success' => true,
                    'transferred' => $amount,
                    'new_balance' => $lockedUser->balance('USD')->value->get(),
                ];
            }, 3);
        } catch (\Throwable $e) {
            Log::error('Secure live earnings transfer failed.', [
                'user_id' => $user->id,
                'idempotency_key' => $idempotencyKey,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Transfer could not be completed. Please try again.',
            ];
        }
    }

    /**
     * Get live analytics backed by database data.
     */
    public function getAnalyticsData(User $user, string $range = '7d'): array
    {
        $dateLimit = match ($range) {
            'today' => now()->startOfDay(),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(7),
        };

        $streams = LiveStreamGumlet::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $dateLimit)
            ->orderByDesc('created_at')
            ->get();

        $streamIds = $streams->pluck('id');

        $giftCoinsByStream = LiveGift::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->select('live_stream_gumlet_id', DB::raw('SUM(coins) as total_coins'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('live_stream_gumlet_id')
            ->get()
            ->keyBy('live_stream_gumlet_id');

        $commentsByStream = LiveComment::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->select('live_stream_gumlet_id', DB::raw('COUNT(*) as total_comments'))
            ->groupBy('live_stream_gumlet_id')
            ->pluck('total_comments', 'live_stream_gumlet_id');

        $subsByStream = PrivateLiveStreamSub::query()
            ->whereIn('stream_id', $streamIds)
            ->select('stream_id', DB::raw('SUM(pay_amount) as total_subs'))
            ->groupBy('stream_id')
            ->pluck('total_subs', 'stream_id');

        $viewersByStream = LiveViewer::query()
            ->with('user:id,new_country')
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->get()
            ->groupBy('live_stream_gumlet_id');

        $sessions = $streams->map(function (LiveStreamGumlet $stream) use ($giftCoinsByStream, $commentsByStream, $subsByStream, $viewersByStream) {
            $viewers = $viewersByStream->get($stream->id, collect());
            $uniqueViewers = $viewers->pluck('user_id')->filter()->unique()->count();
            $watchSeconds = $this->sumViewerWatchSeconds($viewers, $stream);
            $giftRow = $giftCoinsByStream->get($stream->id);
            $coins = (int) ($giftRow->total_coins ?? 0);

            return [
                'id' => $stream->id,
                'date' => optional($stream->created_at)->format('Y-m-d'),
                'title' => $stream->title,
                'category' => $stream->broadcast_type ?? 'Just Chatting',
                'status' => $stream->status,
                'pcu' => (int) max($stream->max_viewers ?? 0, $stream->viewer_count ?? 0),
                'watchHours' => round($watchSeconds / 3600, 2),
                'uniqueViewers' => $uniqueViewers,
                'chats' => (int) ($commentsByStream[$stream->id] ?? 0),
                'reacts' => (int) ($stream->like_count ?? 0),
                'giftCount' => (int) ($giftRow->total_qty ?? 0),
                'coins' => $coins,
                'cash' => round($coins * 0.01, 2),
                'subs' => (float) ($subsByStream[$stream->id] ?? 0),
            ];
        })->values();

        $giftAggregates = LiveGift::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->whereNotNull('linkup_live_gift_id')
            ->select('linkup_live_gift_id as gift_id', DB::raw('SUM(qty) as count'), DB::raw('SUM(coins) as coins'))
            ->groupBy('linkup_live_gift_id')
            ->get();

        $giftMetas = LinkupLiveGift::query()
            ->whereIn('id', $giftAggregates->pluck('gift_id')->filter())
            ->get()
            ->keyBy('id');

        $gifts = $giftAggregates->map(function ($gift) use ($giftMetas) {
            return [
                'gift' => $giftMetas->get($gift->gift_id)?->name ?? 'Gift #'.$gift->gift_id,
                'count' => (int) $gift->count,
                'coins' => (int) $gift->coins,
            ];
        })->sortByDesc('count')->values();

        $subs = PrivateLiveStreamSub::query()
            ->where('user_streamer_id', $user->id)
            ->where('created_at', '>=', $dateLimit)
            ->get();

        $countries = $this->buildCountryAnalytics($viewersByStream, $streams);
        $transfers = $this->buildLiveTransferRows($user);

        return [
            'ok' => true,
            'success' => true,
            'settings' => [
                'coinToUsd' => 0.01,
                'platformFeeRate' => 0.50,
                'subscriptionSplitHost' => 0.50,
                'subscriptionSplitPartner' => 0.50,
            ],
            'sessions' => $sessions,
            'buckets' => $this->buildAnalyticsBuckets($streams, $dateLimit),
            'gifts' => $gifts,
            'subscriptions' => [
                'active' => $subs->where('status', 'active')->count(),
                'revenueGross' => (float) $subs->sum('pay_amount'),
            ],
            'countries' => $countries,
            'transfers' => $transfers,
            'earnings' => $this->getEarningsData($user),
        ];
    }

    private function sumViewerWatchSeconds(Collection $viewers, LiveStreamGumlet $stream): int
    {
        $streamEnd = $stream->ended_at ?? ($stream->status === 'live' ? now() : $stream->updated_at);

        return (int) $viewers->sum(function (LiveViewer $viewer) use ($streamEnd) {
            if (! $viewer->joined_at) {
                return 0;
            }

            $end = $viewer->left_at ?? $viewer->last_heartbeat ?? $streamEnd ?? now();
            if ($end->greaterThan(now())) {
                $end = now();
            }

            return min(max($viewer->joined_at->diffInSeconds($end, false), 0), 43200);
        });
    }

    private function buildCountryAnalytics(Collection $viewersByStream, Collection $streams): Collection
    {
        $streamMap = $streams->keyBy('id');
        $countries = collect();

        $viewersByStream->each(function (Collection $viewers, $streamId) use ($streamMap, $countries) {
            $stream = $streamMap->get($streamId);
            if (! $stream) {
                return;
            }

            $viewers->groupBy(fn (LiveViewer $viewer) => $viewer->user?->new_country ?: 'Unknown')
                ->each(function (Collection $countryViewers, string $country) use ($stream, $countries) {
                    $current = $countries->get($country, [
                        'code' => $country,
                        'name' => $country,
                        'viewers' => 0,
                        'watchHours' => 0,
                    ]);

                    $current['viewers'] += $countryViewers->pluck('user_id')->filter()->unique()->count();
                    $current['watchHours'] += round($this->sumViewerWatchSeconds($countryViewers, $stream) / 3600, 2);
                    $countries->put($country, $current);
                });
        });

        return $countries->values()->sortByDesc('watchHours')->values();
    }

    private function buildLiveTransferRows(User $user): Collection
    {
        return Transaction::query()
            ->where('to_id', $user->id)
            ->where('processor_id', 'live deposit')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'date' => optional($transaction->created_at)->format('Y-m-d H:i'),
                'type' => 'Transfer',
                'ref' => 'WALLET-TRX-'.$transaction->id,
                'amount' => (float) $transaction->amount,
                'status' => 'Completed',
            ]);
    }

    private function buildAnalyticsBuckets(Collection $streams, $dateLimit): array
    {
        $streamIds = $streams->pluck('id');
        $comments = LiveComment::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->where('created_at', '>=', $dateLimit)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as bucket'), DB::raw('COUNT(*) as chats'))
            ->groupBy('bucket')
            ->pluck('chats', 'bucket');

        $gifts = LiveGift::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->where('created_at', '>=', $dateLimit)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as bucket'), DB::raw('SUM(qty) as gifts'))
            ->groupBy('bucket')
            ->pluck('gifts', 'bucket');

        $viewerBuckets = LiveViewer::query()
            ->whereIn('live_stream_gumlet_id', $streamIds)
            ->where('joined_at', '>=', $dateLimit)
            ->get()
            ->groupBy(fn (LiveViewer $viewer) => optional($viewer->joined_at)->format('Y-m-d H:00:00'));

        $hours = [];
        $cursor = $dateLimit->copy()->startOfHour();
        $end = now()->copy()->startOfHour();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:00:00');
            $bucketViewers = $viewerBuckets->get($key, collect());

            $hours[] = [
                't' => $cursor->format($dateLimit->isToday() ? 'H:00' : 'M d H:00'),
                'concurrent' => $bucketViewers->pluck('user_id')->filter()->unique()->count(),
                'watchSeconds' => $bucketViewers->sum(fn (LiveViewer $viewer) => $viewer->joined_at && $viewer->left_at
                    ? max($viewer->joined_at->diffInSeconds($viewer->left_at, false), 0)
                    : 0),
                'chats' => (int) ($comments[$key] ?? 0),
                'reacts' => (int) ($gifts[$key] ?? 0),
            ];

            $cursor->addHour();
        }

        return array_slice($hours, -48);
    }
}
