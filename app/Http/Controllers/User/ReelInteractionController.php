<?php

namespace App\Http\Controllers\User;

use App\Services\VibeActivityNotifier;
use App\Http\Controllers\Controller;
use App\Models\ReelComment;
use App\Models\ReelLike;
use App\Models\ReelSave;
use App\Models\ReelShare;
use App\Models\UserReel;
use App\Services\CoinGiftService;
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
            app(VibeActivityNotifier::class)->liked($user, $reel);
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
            ->withCount('likes')
            ->withExists(['likes as is_liked' => fn ($q) => $q->where('user_id', auth()->id())])
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
                'likes_count' => (int) $comment->likes_count,
                'is_liked' => (bool) $comment->is_liked,
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
            // A reply must answer a comment on this same reel.
            'parent_id' => ['nullable', \Illuminate\Validation\Rule::exists('reel_comments', 'id')->where('user_reel_id', $reel->id)],
        ]);

        $comment = ReelComment::create([
            'user_reel_id' => $reel->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $reel->incrementCommentsCount();

        $repliedTo = $request->parent_id ? ReelComment::find($request->parent_id)?->user : null;
        app(VibeActivityNotifier::class)->commented(auth()->user(), $reel, (string) $request->comment, $comment->id, $repliedTo);

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

    /** Gift on a reel. The coin move is atomic — see CoinGiftService. */
    public function sendGift(Request $request, UserReel $reel): JsonResponse
    {
        $sender = $request->user();
        $receiver = $reel->user;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $data = $request->validate([
            'gift_name' => 'required|string|max:60',
            'emoji' => 'required|string|max:16',
            'coins' => 'required|integer|min:1|max:100000',
        ]);

        if (! $this->giftsAllowed($reel)) {
            return response()->json(['message' => 'Gifts are turned off for this reel.'], 422);
        }

        $coins = (int) $data['coins'];

        $balance = app(CoinGiftService::class)->send($sender, $receiver, $coins, function () use ($sender, $receiver, $reel, $data, $coins) {
            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'reel_id' => $reel->id,
                'name' => $data['gift_name'],
                'emoji' => $data['emoji'],
                'coins' => $coins,
            ]);

            $reel->incrementGiftsCount();

            \App\Models\Notification::create([
                'title'    => 'Gift received!',
                'message'  => "{$sender->name} sent you a '{$data['emoji']} {$data['gift_name']}' on your reel!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'reel_gift',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'reel_id'    => $reel->id,
                    'amount'     => $coins,
                    'gift_name'  => $data['gift_name'],
                    'emoji'      => $data['emoji'],
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'gifts_count' => (int) UserReel::whereKey($reel->id)->value('gifts_count'),
            'user_coins' => $balance,
        ]);
    }

    /** Like a reel comment (reel comments live in `reel_comments`, not `vibe_comments`). */
    public function toggleCommentLike(ReelComment $comment): JsonResponse
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

    /** Big Up on a reel. The coin move is atomic — see CoinGiftService. */
    public function sendBigUp(Request $request, UserReel $reel): JsonResponse
    {
        $sender = $request->user();
        $receiver = $reel->user;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        $data = $request->validate([
            'coins' => 'required|integer|min:1|max:100000',
        ]);

        if (! $this->giftsAllowed($reel)) {
            return response()->json(['message' => 'Big Ups are turned off for this reel.'], 422);
        }

        $coins = (int) $data['coins'];

        $balance = app(CoinGiftService::class)->send($sender, $receiver, $coins, function () use ($sender, $receiver, $reel, $coins) {
            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'reel_id' => $reel->id,
                'name' => 'Big Up',
                'emoji' => '⚡',
                'coins' => $coins,
            ]);

            $reel->incrementBigupsCount();

            \App\Models\Notification::create([
                'title'    => 'Big Up received!',
                'message'  => "{$sender->name} sent you {$coins} coins as a Big Up on your reel!",
                'send_by'  => $sender->id,
                'user_id'  => $receiver->id,
                'type'     => 'gift',
                'context'  => 'reel_bigup',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'reel_id'    => $reel->id,
                    'amount'     => $coins,
                    'sender_name' => $sender->name,
                ]),
            ]);
        });

        return response()->json([
            'bigups_count' => (int) UserReel::whereKey($reel->id)->value('bigups_count'),
            'user_coins' => $balance,
        ]);
    }

    /** Older reels have no `allow_coin_gifts` value; treat that as allowed. */
    private function giftsAllowed(UserReel $reel): bool
    {
        return $reel->allow_coin_gifts === null || (bool) $reel->allow_coin_gifts;
    }
}
