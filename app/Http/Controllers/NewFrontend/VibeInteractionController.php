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

    public function sendBigUp(Request $request, Vibe $vibe): JsonResponse
    {
        $sender = auth()->user();
        $receiver = $vibe->creator;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $request->validate([
            'gift_name' => 'required|string',
            'emoji' => 'required|string',
            'coins' => 'required|integer|min:1',
        ]);

        if ($sender->coins < $request->coins) {
            return response()->json(['message' => 'Insufficient coins'], 422);
        }

        DB::transaction(function () use ($sender, $receiver, $vibe, $request) {
            $sender->decrement('coins', $request->coins);
            $receiver->increment('coins', $request->coins);

            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'vibe_id' => $vibe->id,
                'name' => $request->gift_name,
                'emoji' => $request->emoji,
                'coins' => $request->coins,
            ]);

            $vibe->increment('bigups_count');

            \App\Models\Notification::create([
                'title'    => 'Big Up received!',
                'message'  => "{$sender->name} sent you a '{$request->emoji} {$request->gift_name}' on your vibe!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'vibe_bigup',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'vibe_id'    => $vibe->id,
                    'amount'     => $request->coins,
                    'gift_name'  => $request->gift_name,
                    'emoji'      => $request->emoji,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'bigups_count' => $vibe->bigups_count,
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

        // LinkUp Platform Fee: Remove 5% from the commission
        $linkupFee = $commission * 0.05;
        $finalCommission = $commission - $linkupFee;

        // Ensure a promotion record exists for this creator/product link
        $promotion = \App\Models\MarketplaceAffiliatePromotion::firstOrCreate([
            'user_id' => $vibe->created_by,
            'product_id' => $request->kind === 'product' ? $request->id : null,
        ]);

        \App\Models\MarketplaceAffiliateEarning::create([
            'promotion_id' => $promotion->id,
            'affiliate_user_id' => $vibe->created_by,
            'product_id' => $request->kind === 'product' ? $request->id : null,
            'commission_amount' => $finalCommission,
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

        // LinkUp Platform Fee: Take 5% during transfer to be absolutely sure
        $linkupFee = $totalAmount * 0.05;
        $userPayout = $totalAmount - $linkupFee;

        try {
            DB::transaction(function () use ($user, $earnings, $userPayout) {
                // 1. Mark as paid
                \App\Models\MarketplaceAffiliateEarning::whereIn('id', $earnings->pluck('id'))
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now()
                    ]);

                // 2. Deposit the NET amount to user wallet
                deposit($userPayout, 'USD')
                    ->from(Custodian::of('e_money'))
                    ->to($user)
                    ->overcharge()
                    ->meta(['note' => 'Affiliate commission payout (after 5% platform fee)'])
                    ->commit();
            });

            return response()->json([
                'success' => true,
                'amount' => $userPayout,
                'message' => '$' . number_format($userPayout, 2) . ' transferred to your wallet! (5% LinkUp fee applied)'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }
}
