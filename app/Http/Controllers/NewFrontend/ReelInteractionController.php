<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\UserReel;
use App\Models\VibeComment;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReelInteractionController extends Controller
{
    public function toggleLike(UserReel $reel): JsonResponse
    {
        $user = auth()->user();
        $like = $reel->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $reel->decrementLikesCount();
            $isLiked = false;
        } else {
            $reel->likes()->create([
                'user_id' => $user->id,
                'type' => 'like',
            ]);
            $reel->incrementLikesCount();
            $isLiked = true;
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $reel->likes_count,
        ]);
    }

    public function indexComments(UserReel $reel): JsonResponse
    {
        $comments = $reel->comments()
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

    public function storeComment(Request $request, UserReel $reel): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:vibe_comments,id',
        ]);

        $comment = $reel->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
            'commentable_id' => $reel->id,
            'commentable_type' => UserReel::class,
        ]);

        $reel->incrementCommentsCount();

        return response()->json($comment->load('user:id,name,avatar')->loadCount('likes'), 201);
    }

    public function share(UserReel $reel): JsonResponse
    {
        $reel->incrementSharesCount();

        return response()->json([
            'shares_count' => $reel->shares_count,
        ]);
    }

    public function sendGift(Request $request, UserReel $reel): JsonResponse
    {
        $sender = auth()->user();
        $receiver = $reel->user;

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

        DB::transaction(function () use ($sender, $receiver, $reel, $request) {
            $sender->decrement('coins', $request->coins);
            $receiver->increment('coins', $request->coins);

            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'reel_id' => $reel->id,
                'name' => $request->gift_name,
                'emoji' => $request->emoji,
                'coins' => $request->coins,
            ]);

            $reel->incrementGiftsCount();

            \App\Models\Notification::create([
                'title'    => 'Gift received!',
                'message'  => "{$sender->name} sent you a '{$request->emoji} {$request->gift_name}' on your reel!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'reel_gift',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'reel_id'    => $reel->id,
                    'amount'     => $request->coins,
                    'gift_name'  => $request->gift_name,
                    'emoji'      => $request->emoji,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'gifts_count' => $reel->gifts_count,
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
}
