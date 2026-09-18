<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\ReelComment;
use App\Models\ReelLike;
use App\Models\ReelSave;
use App\Models\ReelShare;
use App\Models\UserReel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReelInteractionController extends Controller
{
    public function toggleLike(UserReel $reel): JsonResponse
    {
        $user = auth()->user();
        $like = ReelLike::where('user_reel_id', $reel->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $reel->decrementLikesCount();
            $isLiked = false;
        } else {
            ReelLike::create([
                'user_reel_id' => $reel->id,
                'user_id' => $user->id,
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
            ->with(['user:id,name,avatar,linkup_id', 'replies.user:id,name,avatar,linkup_id'])
            ->latest()
            ->paginate(20);

        $formattedComments = collect($comments->items())->map(function ($comment) {
            return [
                'id' => $comment->id,
                'user_reel_id' => $comment->user_reel_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                    'linkup_id' => $comment->user->linkup_id,
                ],
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'user_reel_id' => $reply->user_reel_id,
                        'user_id' => $reply->user_id,
                        'comment' => $reply->comment,
                        'parent_id' => $reply->parent_id,
                        'created_at' => $reply->created_at->diffForHumans(),
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'avatar' => $reply->user->avatar,
                            'linkup_id' => $reply->user->linkup_id,
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'comments' => $formattedComments,
            'comments_count' => $reel->comments_count,
        ]);
    }

    public function storeComment(Request $request, UserReel $reel): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:reel_comments,id',
        ]);

        $comment = ReelComment::create([
            'user_reel_id' => $reel->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $reel->incrementCommentsCount();

        $comment->load('user:id,name,avatar,linkup_id');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'user_reel_id' => $comment->user_reel_id,
                'user_id' => $comment->user_id,
                'comment' => $comment->comment,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                    'linkup_id' => $comment->user->linkup_id,
                ],
            ],
            'comments_count' => $reel->comments_count,
        ], 201);
    }

    public function share(UserReel $reel): JsonResponse
    {
        $user = auth()->user();

        // Only record the share if not already shared by this user
        $existingShare = ReelShare::where('user_reel_id', $reel->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$existingShare) {
            ReelShare::create([
                'user_reel_id' => $reel->id,
                'user_id' => $user->id,
            ]);
            $reel->incrementSharesCount();
        }

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

    public function toggleSave(UserReel $reel): JsonResponse
    {
        $user = auth()->user();
        $save = ReelSave::where('user_reel_id', $reel->id)
            ->where('user_id', $user->id)
            ->first();

        if ($save) {
            $save->delete();
            $isSaved = false;
        } else {
            ReelSave::create([
                'user_reel_id' => $reel->id,
                'user_id' => $user->id,
            ]);
            $isSaved = true;
        }

        return response()->json([
            'is_saved' => $isSaved,
        ]);
    }

    public function sendBigUp(Request $request, UserReel $reel): JsonResponse
    {
        $sender = auth()->user();
        $receiver = $reel->user;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $request->validate([
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
                'name' => 'Big Up',
                'emoji' => '⚡',
                'coins' => $request->coins,
            ]);

            $reel->incrementBigupsCount();

            \App\Models\Notification::create([
                'title'    => 'Big Up received!',
                'message'  => "{$sender->name} sent you {$request->coins} coins as a Big Up on your reel!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'reel_bigup',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'reel_id'    => $reel->id,
                    'amount'     => $request->coins,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'bigups_count' => $reel->bigups_count,
            'user_coins' => $sender->fresh()->coins,
        ]);
    }
}
