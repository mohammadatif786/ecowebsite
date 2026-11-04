<?php

namespace App\Http\Controllers\User;

use App\Services\VibeActivityNotifier;
use App\Domain\Vibes\Actions\PostVibeCommentAction;
use App\Domain\Vibes\Actions\ToggleVibeLikeAction;
use App\Http\Controllers\Controller;
use App\Models\MarketplaceAffiliateEarning;
use App\Models\User;
use App\Models\Vibe;
use App\Models\VibeComment;
use App\Services\CoinGiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use O21\LaravelWallet\Models\Custodian;

class VibeInteractionController extends Controller
{
    /** Commission statuses that can be moved to the wallet (vibe sales use "released", marketplace orders "available"). */
    private const PAYABLE_STATUSES = ['released', 'available'];

    /** 404 for posts the user isn't allowed to see (private, friends-only, blocked). */
    private function ensureVisible(Vibe $vibe): void
    {
        abort_unless($vibe->isVisibleTo(auth()->user()), 404, 'Post not found.');
    }

    public function toggleLike(Vibe $vibe, ToggleVibeLikeAction $action): JsonResponse
    {
        $this->ensureVisible($vibe);
        $result = $action->execute(auth()->user(), $vibe);

        if (! empty($result['is_liked'])) {
            app(VibeActivityNotifier::class)->liked(auth()->user(), $vibe);
        }

        return response()->json($result);
    }

    public function indexComments(Vibe $vibe): JsonResponse
    {
        $this->ensureVisible($vibe);
        $comments = $vibe->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
            ->withCount(['likes', 'replies'])
            ->latest()
            ->paginate(20);

        // Add is_liked status for auth user
        if (auth()->check()) {
            $comments->getCollection()->each(function ($comment) {
                $comment->is_liked = $comment->likes()->where('user_id', auth()->id())->exists();
                $comment->replies->each(function ($reply) {
                    $reply->is_liked = $reply->likes()->where('user_id', auth()->id())->exists();
                    $reply->likes_count = $reply->likes()->count();
                });
            });
        }

        return response()->json($comments);
    }

    public function topComments(Vibe $vibe): JsonResponse
    {
        $this->ensureVisible($vibe);
        $comments = $vibe->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar'])
            ->withCount('likes')
            ->latest()
            ->take(3)
            ->get();

        if (auth()->check()) {
            $comments->each(function ($comment) {
                $comment->is_liked = $comment->likes()->where('user_id', auth()->id())->exists();
            });
        }

        return response()->json($comments);
    }

    public function storeComment(Request $request, Vibe $vibe, PostVibeCommentAction $action): JsonResponse
    {
        $this->ensureVisible($vibe);
        $request->validate([
            'comment' => 'required|string|max:1000',
            // A reply must answer a comment on this same post.
            'parent_id' => ['nullable', \Illuminate\Validation\Rule::exists('vibe_comments', 'id')->where('vibe_id', $vibe->id)],
        ]);

        $comment = $vibe->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $vibe->increment('comments_count');

        $repliedTo = $request->parent_id ? \App\Models\VibeComment::find($request->parent_id)?->user : null;
        app(VibeActivityNotifier::class)->commented(auth()->user(), $vibe, (string) $request->comment, $comment->id, $repliedTo);

        return response()->json($comment->load('user:id,name,avatar')->loadCount('likes'), 201);
    }

    public function share(Vibe $vibe): JsonResponse
    {
        $this->ensureVisible($vibe);
        $vibe->increment('shares_count');

        return response()->json([
            'shares_count' => $vibe->shares_count,
        ]);
    }

    /**
     * Big Up: send coins to a post's creator (or, with no post, to `recipient_id`).
     * The coin move is atomic — see CoinGiftService.
     */
    public function sendBigUp(Request $request, ?Vibe $vibe = null): JsonResponse
    {
        $sender = $request->user();

        $data = $request->validate([
            'gift_name' => 'nullable|string|max:60',
            'emoji' => 'nullable|string|max:16',
            'coins' => 'required|integer|min:1|max:100000',
            'recipient_id' => 'nullable|integer',
        ]);

        if ($vibe) {
            $this->ensureVisible($vibe);
            if (! $vibe->allow_coin_gifts) {
                return response()->json(['message' => 'Big Ups are turned off for this post.'], 422);
            }
            // A Big Up on a post always goes to that post's creator.
            $receiver = User::find($vibe->created_by);
        } else {
            $receiver = isset($data['recipient_id']) ? User::find($data['recipient_id']) : null;
        }

        if (! $receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $coins = (int) $data['coins'];
        $giftName = $data['gift_name'] ?? 'Big Up';
        $emoji = $data['emoji'] ?? '⚡';

        $balance = app(CoinGiftService::class)->send($sender, $receiver, $coins, function () use ($sender, $receiver, $vibe, $coins, $giftName, $emoji) {
            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'vibe_id' => $vibe?->id,
                'source' => 'vibe',
                'name' => $giftName,
                'emoji' => $emoji,
                'coins' => $coins,
            ]);

            if ($vibe) {
                $vibe->increment('bigups_count');
            }

            \App\Models\Notification::create([
                'title'    => 'Gift received!',
                'message'  => "{$sender->name} sent you a '{$emoji} {$giftName}'!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'vibe_bigup',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'vibe_id'    => $vibe?->id,
                    'amount'     => $coins,
                    'gift_name'  => $giftName,
                    'emoji'      => $emoji,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'bigups_count' => $vibe ? (int) Vibe::whereKey($vibe->id)->value('bigups_count') : 0,
            'user_coins' => $balance,
        ]);
    }

    public function toggleCommentLike(VibeComment $comment): JsonResponse
    {
        $user = auth()->user();
        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            $comment->likes()->create([
                'user_id' => $user->id,
                'type' => 'like',
            ]);
            $isLiked = true;
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    public function purchaseAttachment(Request $request, Vibe $vibe): JsonResponse
    {
        $user = auth()->user();
        $request->validate([
            'kind' => 'required|in:product,event',
            'id' => 'required|integer',
            'price' => 'required|numeric'
        ]);

        $price = (float) $request->price;
        $walletBalance = (float) ($user->balance('USD')->value->get() ?? 0);

        if ($walletBalance < $price) {
            return response()->json(['message' => 'Insufficient wallet balance'], 422);
        }

        // 1. Deduct funds from buyer and transfer to system custodian
        transfer($price, 'USD')
            ->from($user)
            ->to(Custodian::of('e_money'))
            ->overcharge(false)
            ->meta(['note' => "Purchased tagged {$request->kind} on Vibe #{$vibe->id}"])
            ->commit();

        // 2. Calculate and record Affiliate Commission
        // Use the product's actual commission rate if available, otherwise default to 10%
        $product = \App\Models\MarketplaceProduct::find($request->id);
        $commissionRate = 0.10;

        if ($product) {
            if ($product->commMode === 'pct') {
                $commissionRate = $product->commission / 100;
            } elseif ($product->commMode === 'flat') {
                // If flat, we calculate a virtual rate for this one sale
                $commissionRate = ($product->commFlat > 0 && $price > 0) ? ($product->commFlat / $price) : 0.10;
            }
        }

        $commission = $price * $commissionRate;

        // Ensure a promotion record exists for this creator/product link
        $promotion = \App\Models\MarketplaceAffiliatePromotion::firstOrCreate([
            'user_id' => $vibe->created_by,
            'product_id' => $request->kind === 'product' ? $request->id : null,
        ]);

        \App\Models\MarketplaceAffiliateEarning::create([
            'promotion_id' => $promotion->id,
            'affiliate_user_id' => $vibe->created_by,
            'product_id' => $request->kind === 'product' ? $request->id : null,
            'commission_amount' => round($commission, 2),
            'status' => 'pending', // New earnings start as pending
        ]);

        return response()->json([
            'message' => 'Purchase successful',
            'new_balance' => (float) $user->balance('USD')->value->get()
        ]);
    }

    /**
     * Releases the user's pending commissions from Vibe sales once the hold period
     * (config vibes.affiliate_hold_days) has passed. Marketplace order commissions are
     * released only when the buyer confirms delivery (ReleaseMarketplaceAffiliateCommissionAction),
     * so a user can no longer release their own commissions straight away.
     */
    public function releasePendingEarnings(): JsonResponse
    {
        $user = auth()->user();
        $holdDays = (int) config('vibes.affiliate_hold_days', 7);

        $updated = MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->where('status', 'pending')
            ->whereNull('order_id')
            ->where('created_at', '<=', now()->subDays($holdDays))
            ->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

        $stillPending = MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        if ($updated > 0) {
            $message = "Released {$updated} commission".($updated === 1 ? '' : 's').'.';
        } elseif ($stillPending > 0) {
            $message = "Nothing to release yet — commissions are released {$holdDays} days after the sale, or when the buyer confirms delivery.";
        } else {
            $message = 'No pending commissions.';
        }

        return response()->json([
            'success' => true,
            'count' => $updated,
            'still_pending' => $stillPending,
            'message' => $message,
        ]);
    }

    /**
     * Moves all available commissions to the wallet (minus the 5% LinkUp fee).
     * The rows are locked first, so two transfers at the same moment can't pay
     * the same commission twice.
     */
    public function transferToWallet(): JsonResponse
    {
        $user = auth()->user();

        try {
            $result = DB::transaction(function () use ($user) {
                $earnings = MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
                    ->whereIn('status', self::PAYABLE_STATUSES)
                    ->lockForUpdate()
                    ->get();

                $totalAmount = round((float) $earnings->sum('commission_amount'), 2);
                if ($totalAmount <= 0) {
                    return null;
                }

                // LinkUp Platform Fee: take 5% during transfer before sending to wallet
                $linkupFee = round($totalAmount * 0.05, 2);
                $userPayout = round($totalAmount - $linkupFee, 2);

                $marked = MarketplaceAffiliateEarning::whereIn('id', $earnings->pluck('id'))
                    ->whereIn('status', self::PAYABLE_STATUSES)
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                if ($marked !== $earnings->count()) {
                    throw new \RuntimeException('Commission rows changed during the transfer.');
                }

                // Deposit the NET amount (95%) to the user's wallet
                deposit($userPayout, 'USD')
                    ->from(Custodian::of('e_money'))
                    ->to($user)
                    ->overcharge()
                    ->meta([
                        'type' => 'vibe_commission_transfer',
                        'processor_id' => 'vibe commission',
                        'note' => 'Affiliate commission payout (after 5% LinkUp fee)',
                        'gross_amount' => $totalAmount,
                        'platform_fee' => $linkupFee,
                        'net_payout' => $userPayout,
                    ])
                    ->commit();

                return ['payout' => $userPayout, 'fee' => $linkupFee];
            });
        } catch (\Throwable $e) {
            Log::error('Vibe commission transfer failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Transfer failed. Please try again.'], 500);
        }

        if ($result === null) {
            return response()->json(['message' => 'Nothing available to transfer'], 422);
        }

        return response()->json([
            'success' => true,
            'amount' => $result['payout'],
            'linkup_fee' => $result['fee'],
            'message' => '$'.number_format($result['payout'], 2).' transferred to your wallet! (5% LinkUp fee applied: $'.number_format($result['fee'], 2).')',
        ]);
    }

    public function repost(Vibe $vibe): JsonResponse
    {
        $user = auth()->user();
        $this->ensureVisible($vibe);

        if ((int) $vibe->created_by === (int) $user->id) {
            return response()->json(['message' => 'That’s already your own post.'], 422);
        }
        if ($vibe->visibility !== \App\Domain\Vibes\Enums\VibeVisibility::Public) {
            return response()->json(['message' => 'Only public posts can be reposted.'], 422);
        }
        if (Vibe::where('created_by', $user->id)->where('reposted_from_id', $vibe->id)->exists()) {
            return response()->json(['message' => 'You already reposted this.'], 422);
        }

        // Keep who posted it first, so the repost credits the original creator.
        $vibe->loadMissing('publisher', 'creator');
        $originalName = collect([$vibe->publisher?->name, $vibe->publisher?->organizer_name, $vibe->creator?->name])
            ->map(fn ($name) => trim((string) $name))
            ->first(fn ($name) => $name !== '') ?? 'LinkUp member';

        $newVibe = DB::transaction(function () use ($user, $vibe, $originalName) {
            $newVibe = Vibe::create([
                'created_by' => $user->id,
                'publisher_type' => 'user',
                'publisher_id' => $user->id,
                'reposted_from_id' => $vibe->id,
                'reposted_from_name' => mb_substr((string) $originalName, 0, 120),
                'caption' => $vibe->caption,
                'text_bg' => $vibe->text_bg,
                'text_font' => $vibe->text_font,
                'location_name' => $vibe->location_name,
                'location_place_id' => $vibe->location_place_id,
                'latitude' => $vibe->latitude,
                'longitude' => $vibe->longitude,
                'allow_coin_gifts' => $vibe->allow_coin_gifts,
                'visibility' => 'public',
                'status' => \App\Domain\Vibes\Enums\VibeStatus::Published,
                'published_at' => now(),
            ]);

            foreach ($vibe->media as $m) {
                $newVibe->media()->create([
                    'media_type' => $m->media_type,
                    'source' => $m->source,
                    'disk' => $m->disk,
                    'path' => $m->path,
                    'thumbnail_path' => $m->thumbnail_path,
                    'original_name' => $m->original_name,
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                    'width' => $m->width,
                    'height' => $m->height,
                    'duration' => $m->duration,
                    'processing_status' => $m->processing_status,
                    'metadata' => $m->metadata,
                    'sort_order' => $m->sort_order,
                ]);
            }

            $productIds = $vibe->products()->pluck('products.id')->toArray();
            $eventIds = $vibe->events()->pluck('link_up_events.id')->toArray();

            if (!empty($productIds)) {
                $newVibe->products()->sync($productIds);
            }
            if (!empty($eventIds)) {
                $newVibe->events()->sync($eventIds);
            }

            $vibe->increment('reposts_count');

            app(VibeActivityNotifier::class)->reposted($user, $vibe);

            return $newVibe;
        });

        $mediaItems = $newVibe->media->map(function ($m) {
            return [
                'id' => $m->id,
                'type' => $m->media_type,
                'url' => asset('storage/' . $m->path),
                'thumbnail' => $m->thumbnail_path ? asset('storage/' . $m->thumbnail_path) : null,
            ];
        });

        return response()->json([
            'is_reposed' => true,
            'reposts_count' => $vibe->fresh()->reposts_count,
            'new_vibe' => [
                'id' => $newVibe->id,
                'created_by' => $newVibe->created_by,
                'handle' => $user->name,
                'publisher_type' => 'user',
                'avatar' => $user->avatar,
                'location' => $newVibe->location_name,
                'media' => $mediaItems,
                'kind' => !empty($newVibe->text_bg) && $mediaItems->isEmpty() ? 'text' : 'photo',
                'caption' => $newVibe->caption,
                'text_bg' => $newVibe->text_bg,
                'text_font' => $newVibe->text_font,
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'bigups_count' => 0,
                'is_liked' => false,
                'allow_coin_gifts' => (bool) $newVibe->allow_coin_gifts,
                'bigup' => 0,
                'shoppable' => false,
                'tag' => null,
            ]
        ]);
    }

    public function earningsSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        $affEarnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->latest()
            ->get();

        $pendingAff = (float) $affEarnings->where('status', 'pending')->sum('commission_amount');
        $availableAff = (float) $affEarnings->whereIn('status', self::PAYABLE_STATUSES)->sum('commission_amount');
        $paidAff = (float) $affEarnings->where('status', 'paid')->sum('commission_amount');

        $earningsStats = [
            'sales_driven' => $affEarnings->count(),
            'total_commission' => (float) $affEarnings->sum('commission_amount'),
            'pending' => $pendingAff,
            'available' => $availableAff,
            'paid' => $paidAff,
            'recent' => $affEarnings->take(20)->map(fn ($e) => $e->summaryRow())
        ];

        return response()->json([
            'success' => true,
            'earnings' => $earningsStats,
        ]);
    }
}
