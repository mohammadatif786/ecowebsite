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

        // 1. Deduct funds from buyer
        withdraw($price, 'USD')
            ->to($user)
            ->overcharge(false)
            ->meta(['note' => "Purchased tagged {$request->kind} on Vibe #{$vibe->id}"])
            ->commit();

        // 2. Calculate and record Affiliate Commission
        // For simulation, we'll give 10% commission to the Vibe creator
        $commission = $price * 0.10;

        \App\Models\MarketplaceAffiliateEarning::create([
            'affiliate_user_id' => $vibe->created_by,
            'product_id' => $request->kind === 'product' ? $request->id : null,
            'commission_amount' => $commission,
            'status' => 'released', // Immediate release for testing
            'released_at' => now(),
        ]);

        return response()->json([
            'message' => 'Purchase successful',
            'new_balance' => (float) $user->balance('USD')->value->get()
        ]);
    }
}
