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
use App\Support\VibeGiftCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReelInteractionController extends Controller
{
    /** 404 for reels the member may not see (inactive, or hidden by a block either way). */
    private function ensureVisible(UserReel $reel): void
    {
        abort_unless($reel->isVisibleTo(auth()->user()), 404, 'Reel not found.');
    }

    /** Like / unlike. The reel row is locked and the count set from the real likes, so double taps can't drift it. */
    public function toggleLike(UserReel $reel): JsonResponse
    {
        $this->ensureVisible($reel);
        $user = auth()->user();

        [$isLiked, $count] = DB::transaction(function () use ($reel, $user) {
            UserReel::query()->whereKey($reel->id)->lockForUpdate()->first();

            $isLiked = ! ReelLike::where('user_reel_id', $reel->id)->where('user_id', $user->id)->exists();
            if ($isLiked) {
                ReelLike::create(['user_reel_id' => $reel->id, 'user_id' => $user->id]);
            } else {
                ReelLike::where('user_reel_id', $reel->id)->where('user_id', $user->id)->delete();
            }

            $count = ReelLike::where('user_reel_id', $reel->id)->count();
            UserReel::query()->whereKey($reel->id)->update(['likes_count' => $count]);

            return [$isLiked, $count];
        });

        if ($isLiked) {
            app(VibeActivityNotifier::class)->liked($user, $reel);
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $count,
        ]);
    }

    public function indexComments(UserReel $reel): JsonResponse
    {
        $this->ensureVisible($reel);
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
        $this->ensureVisible($reel);
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
        $this->ensureVisible($reel);
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
        $this->ensureVisible($reel);
        $sender = $request->user();
        $receiver = $reel->user;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        // The gift's name, emoji and price come from the catalog, never from the app.
        $gift = VibeGiftCatalog::resolve($request->validate(VibeGiftCatalog::rules()), allowCustom: false);

        if (! $this->giftsAllowed($reel)) {
            return response()->json(['message' => 'Gifts are turned off for this reel.'], 422);
        }

        $coins = $gift['coins'];
        $data = ['gift_name' => $gift['name'], 'emoji' => $gift['emoji']];

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
        $reel = UserReel::find($comment->user_reel_id);
        abort_unless($reel, 404);
        $this->ensureVisible($reel);
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
        $this->ensureVisible($reel);
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
        $this->ensureVisible($reel);
        $sender = $request->user();
        $receiver = $reel->user;

        if (!$receiver) {
            return response()->json(['message' => 'Creator not found'], 404);
        }

        // A catalog Big Up (`gift_id`) at its catalog price, or a plain "Big Up ⚡" for any amount.
        $gift = VibeGiftCatalog::resolve($request->validate(VibeGiftCatalog::rules()));

        if (! $this->giftsAllowed($reel)) {
            return response()->json(['message' => 'Big Ups are turned off for this reel.'], 422);
        }

        $coins = $gift['coins'];

        $balance = app(CoinGiftService::class)->send($sender, $receiver, $coins, function () use ($sender, $receiver, $reel, $coins, $gift) {
            \App\Models\GiftCoins::create([
                'sender_id' => $sender->id,
                'recieved_id' => $receiver->id,
                'reel_id' => $reel->id,
                'name' => $gift['name'],
                'emoji' => $gift['emoji'],
                'coins' => $coins,
            ]);

            $reel->incrementBigupsCount();

            \App\Models\Notification::create([
                'title'    => 'Big Up received!',
                'message'  => "{$sender->name} sent you a '{$gift['emoji']} {$gift['name']}' ({$coins} coins) on your reel!",
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

    /**
     * Delete a comment: the person who wrote it, or the reel's creator, can. Replies go with it,
     * and the reel's comment count is set from what is left.
     */
    public function destroyComment(ReelComment $comment): JsonResponse
    {
        $user = auth()->user();
        $reel = UserReel::find($comment->user_reel_id);

        $allowed = (int) $comment->user_id === (int) $user->id
            || ($reel && (int) $reel->user_id === (int) $user->id);
        abort_unless($allowed, 403, 'You can only delete your own comments.');

        $count = DB::transaction(function () use ($comment, $reel) {
            ReelComment::where('parent_id', $comment->id)->delete();
            $comment->delete();

            if (! $reel) {
                return 0;
            }
            $count = ReelComment::where('user_reel_id', $reel->id)->count();
            UserReel::query()->whereKey($reel->id)->update(['comments_count' => $count]);

            return $count;
        });

        return response()->json(['success' => true, 'comments_count' => $count]);
    }
}
