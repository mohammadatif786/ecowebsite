<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Vibes\Actions\PostVibeCommentAction;
use App\Domain\Vibes\Actions\ToggleVibeLikeAction;
use App\Http\Controllers\Controller;
use App\Models\Vibe;
use App\Models\VibeComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use O21\LaravelWallet\Models\Custodian;

class VibeInteractionController extends Controller
{
    public function toggleLike(Vibe $vibe, ToggleVibeLikeAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user(), $vibe);

        return response()->json($result);
    }

    public function indexComments(Vibe $vibe): JsonResponse
    {
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
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:vibe_comments,id',
        ]);

        $comment = $vibe->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $vibe->increment('comments_count');

        return response()->json($comment->load('user:id,name,avatar')->loadCount('likes'), 201);
    }

    public function share(Vibe $vibe): JsonResponse
    {
        $vibe->increment('shares_count');

        return response()->json([
            'shares_count' => $vibe->shares_count,
        ]);
    }

    public function sendBigUp(Request $request, ?Vibe $vibe = null): JsonResponse
    {
        $sender = auth()->user();
        $recipientId = $request->input('recipient_id') ?? $vibe?->created_by;

        if (!$recipientId) {
            return response()->json(['message' => 'Recipient not found'], 404);
        }

        $receiver = \App\Models\User::find($recipientId);

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $request->validate([
            'gift_name' => 'nullable|string',
            'emoji' => 'nullable|string',
            'coins' => 'required|integer|min:1',
        ]);

        $giftName = $request->input('gift_name', 'Big Up');
        $emoji = $request->input('emoji', '⚡');

        if ($sender->coins < $request->coins) {
            return response()->json(['message' => 'Insufficient coins'], 422);
        }

        DB::transaction(function () use ($sender, $receiver, $vibe, $request, $giftName, $emoji) {
            $sender->decrement('coins', $request->coins);
            $receiver->increment('coins', $request->coins);

            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'vibe_id' => $vibe?->id,
                'source' => 'vibe',
                'name' => $giftName,
                'emoji' => $emoji,
                'coins' => $request->coins,
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
                    'amount'     => $request->coins,
                    'gift_name'  => $giftName,
                    'emoji'      => $emoji,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'bigups_count' => $vibe?->bigups_count ?? 0,
            'user_coins' => $sender->fresh()->coins,
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

    public function releasePendingEarnings(): JsonResponse
    {
        $user = auth()->user();

        $updated = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'released',
                'released_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'count' => $updated,
            'message' => "Successfully released {$updated} pending commissions."
        ]);
    }

    public function transferToWallet(): JsonResponse
    {
        $user = auth()->user();

        $earnings = \App\Models\MarketplaceAffiliateEarning::where('affiliate_user_id', $user->id)
            ->where('status', 'released')
            ->get();

        $totalAmount = (float) $earnings->sum('commission_amount');

        if ($totalAmount <= 0) {
            return response()->json(['message' => 'Nothing available to transfer'], 422);
        }

        // LinkUp Platform Fee: Take 5% during transfer before sending to wallet
        $linkupFee = round($totalAmount * 0.05, 2);
        $userPayout = round($totalAmount - $linkupFee, 2);

        try {
            DB::transaction(function () use ($user, $earnings, $userPayout, $linkupFee, $totalAmount) {
                // 1. Mark as paid
                \App\Models\MarketplaceAffiliateEarning::whereIn('id', $earnings->pluck('id'))
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now()
                    ]);

                // 2. Deposit the NET amount (95%) to user wallet
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
            });

            return response()->json([
                'success' => true,
                'amount' => $userPayout,
                'linkup_fee' => $linkupFee,
                'message' => '$' . number_format($userPayout, 2) . ' transferred to your wallet! (5% LinkUp fee applied: $' . number_format($linkupFee, 2) . ')'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }

    public function repost(Vibe $vibe): JsonResponse
    {
        $user = auth()->user();

        $newVibe = DB::transaction(function () use ($user, $vibe) {
            $newVibe = Vibe::create([
                'created_by' => $user->id,
                'publisher_type' => 'user',
                'publisher_id' => $user->id,
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
}
