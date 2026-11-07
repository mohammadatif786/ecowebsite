<?php

namespace App\Http\Controllers\User;

use App\Domain\Vibes\Actions\CreateVibeAction;
use App\Domain\Vibes\DTOs\CreateVibeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vibes\StoreVibeRequest;
use App\Http\Resources\VibeResource;
use App\Models\BlockedUser;
use App\Models\CreatorFollower;
use App\Models\User;
use App\Models\Vibe;
use App\Services\UVibePostActionService;
use App\Services\VibeActivityNotifier;
use App\Support\UVibeOptions;
use App\Support\VibeFeedPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VibeController extends Controller
{
    /** Feed page size; `?page=2` loads the next page. */
    private const FEED_PAGE_SIZE = 20;

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        $page = max(1, (int) $request->query('page', 1));
        // `?tag=fete` (or `#fete`): only posts with that hashtag.
        $tag = strtolower(ltrim(trim((string) $request->query('tag', '')), '#'));

        $vibes = VibeFeedPresenter::query($user)
            ->when($tag !== '', fn ($q) => $q->whereIn('id', DB::table('vibe_hashtags')->where('tag', $tag)->select('vibe_id')))
            ->latest()
            ->skip(($page - 1) * self::FEED_PAGE_SIZE)
            ->take(self::FEED_PAGE_SIZE + 1)
            ->get();

        $hasMore = $vibes->count() > self::FEED_PAGE_SIZE;

        return response()->json([
            'posts' => $vibes->take(self::FEED_PAGE_SIZE)->map(fn (Vibe $vibe) => VibeFeedPresenter::present($vibe))->values(),
            'page' => $page,
            'has_more' => $hasMore,
            'tag' => $tag === '' ? null : $tag,
        ]);
    }

    /** One post, in the feed's shape — for links, notifications and "View post". */
    public function show(\Illuminate\Http\Request $request, Vibe $vibe): JsonResponse
    {
        $post = VibeFeedPresenter::query($request->user())->whereKey($vibe->id)->first();
        abort_if($post === null, 404, 'This post isn’t available.');

        return response()->json(['post' => VibeFeedPresenter::present($post)]);
    }

    /** "Hide this post": it disappears from this member's feeds only. */
    public function hide(\Illuminate\Http\Request $request, Vibe $vibe, UVibePostActionService $actions): JsonResponse
    {
        abort_unless($vibe->isVisibleTo($request->user()), 404);
        $actions->hide($request->user(), $vibe);

        return response()->json(['success' => true, 'message' => 'Post hidden. You won’t see it in your feed.']);
    }

    /** Report a post to the moderation team (`reason` from `vibes/report-reasons`). */
    public function report(\Illuminate\Http\Request $request, Vibe $vibe, UVibePostActionService $actions): JsonResponse
    {
        abort_unless($vibe->isVisibleTo($request->user()), 404);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:24'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $actions->report($request->user(), $vibe, $data['reason'], $data['notes'] ?? null);

        return response()->json([
            'success' => true,
            'already' => $result['already'],
            'message' => $result['already'] ? 'You already reported this post — our team is on it.' : 'Thanks — our team will review this post.',
        ]);
    }

    /** What a member can report a post for (the same list U Vibe uses). */
    public function reportReasons(): JsonResponse
    {
        return response()->json([
            'reasons' => collect(UVibeOptions::REPORT_REASONS)->map(fn ($r) => [
                'key' => $r[0],
                'emoji' => $r[1],
                'label' => $r[2],
                'description' => $r[3],
                'severity' => $r[4],
            ])->values(),
        ]);
    }

    public function store(StoreVibeRequest $request, CreateVibeAction $action): JsonResponse
    {
        $vibe = $action->execute($request->user(), CreateVibeData::fromRequest($request));
        app(VibeActivityNotifier::class)->mentioned($request->user(), $vibe, $vibe->caption);

        return (new VibeResource($vibe))->response()->setStatusCode(201);
    }

    public function update(\Illuminate\Http\Request $request, Vibe $vibe): JsonResponse
    {
        // Only the person who posted it can change it.
        abort_unless((int) $vibe->created_by === (int) auth()->id(), 403, 'Unauthorized');

        $request->validate([
            'caption' => 'nullable|string|max:'.config('vibes.caption_max', 2200),
            'location_name' => 'nullable|string|max:255',
            'text_bg' => ['nullable', 'string', 'max:255', 'regex:'.config('vibes.text_bg_pattern')],
            'text_font' => ['nullable', 'string', 'max:100', 'regex:'.config('vibes.text_font_pattern')],
        ]);

        $oldCaption = (string) $vibe->caption;
        $caption = $request->has('caption') ? trim((string) $request->input('caption')) : $oldCaption;

        if ($caption === '' && ! $vibe->media()->exists()) {
            return response()->json(['message' => 'A post needs words or at least one photo or video.'], 422);
        }

        DB::transaction(function () use ($request, $vibe, $caption, $oldCaption) {
            $vibe->update([
                'caption' => $caption,
                'location_name' => $request->input('location_name', $vibe->location_name),
                'text_bg' => $request->input('text_bg', $vibe->text_bg),
                'text_font' => $request->input('text_font', $vibe->text_font),
                'edited_at' => $caption !== $oldCaption ? now() : $vibe->edited_at,
            ]);

            // Hashtags follow the words.
            if ($caption !== $oldCaption) {
                $vibe->syncHashtags($caption);
            }
        });

        // Tell only the people newly @mentioned by the edit.
        if ($caption !== $oldCaption) {
            preg_match_all('/(?<![\w@])@([A-Za-z0-9_.]{2,40})/u', $oldCaption, $m);
            $already = $m[1] ? User::whereIn('linkup_id', array_map(fn ($h) => rtrim($h, '.'), $m[1]))->pluck('id')->all() : [];
            app(VibeActivityNotifier::class)->mentioned($request->user(), $vibe, $caption, array_merge([$request->user()->id], array_map('intval', $already)));
        }

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'vibe' => $vibe->fresh(),
        ]);
    }

    public function destroy(Vibe $vibe): JsonResponse
    {
        // Only the person who posted it can delete it.
        abort_unless((int) $vibe->created_by === (int) auth()->id(), 403, 'Unauthorized');

        $vibe->delete();

        return response()->json(['success' => true]);
    }

    public function copyLink(Vibe $vibe): JsonResponse
    {
        $shareUrl = url("/user/vibes?id={$vibe->id}");

        return response()->json([
            'success' => true,
            'share_url' => $shareUrl,
        ]);
    }

    public function suggestedCreators(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        $followingIds = CreatorFollower::followedIds($user->id);

        $suggestedCreators = User::query()
            ->where('id', '!=', $user->id)
            ->where(fn ($query) => $query->whereHas('vibes')->orWhereHas('reels'))
            ->whereNotIn('id', BlockedUser::query()->where('user_id', $user->id)->where('is_blocked', true)->select('blocked_user_id'))
            ->whereNotIn('id', BlockedUser::query()->where('blocked_user_id', $user->id)->where('is_blocked', true)->select('user_id'))
            ->latest()
            ->limit(30)
            ->get(['id', 'name', 'linkup_id', 'avatar'])
            ->map(fn ($u) => [
                'user_id' => $u->id,
                'handle' => $u->linkup_id ?? $u->name ?? 'User',
                'name' => $u->name,
                'avatar' => $u->avatar ?: ('https://i.pravatar.cc/150?u=' . $u->id),
                'is_following' => in_array((int) $u->id, $followingIds, true),
            ])
            ->values();

        return response()->json([
            'suggested_creators' => $suggestedCreators,
        ]);
    }

    /**
     * Follow / unfollow a creator. One-way: it no longer creates an accepted
     * friend request, so following someone doesn't make you their friend.
     */
    public function toggleFollowCreator(User $user): JsonResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        $blocked = BlockedUser::query()->where('is_blocked', true)->where(function ($q) use ($currentUser, $user) {
            $q->where(fn ($a) => $a->where('user_id', $currentUser->id)->where('blocked_user_id', $user->id))
                ->orWhere(fn ($b) => $b->where('user_id', $user->id)->where('blocked_user_id', $currentUser->id));
        })->exists();
        if ($blocked) {
            return response()->json(['message' => 'You can’t follow this user.'], 422);
        }

        $deleted = CreatorFollower::where('follower_id', $currentUser->id)->where('creator_id', $user->id)->delete();
        if ($deleted > 0) {
            $isFollowing = false;
        } else {
            CreatorFollower::firstOrCreate(['follower_id' => $currentUser->id, 'creator_id' => $user->id]);
            $isFollowing = true;
            app(VibeActivityNotifier::class)->followed($currentUser, $user);
        }

        $userReels = [];
        if ($isFollowing) {
            $userReels = \App\Models\UserReel::active()
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($reel) use ($currentUser) {
                    return [
                        'id' => $reel->id,
                        'uid' => $reel->uid,
                        'user_id' => $reel->user_id,
                        'handle' => $reel->user_id === $currentUser->id ? 'Your Reel' : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
                        'avatar' => $reel->user?->avatar,
                        'name' => $reel->user?->name ?? 'User',
                        'type' => $reel->type,
                        'file_path' => $reel->file_path,
                        'thumbnail_path' => $reel->thumbnail_path,
                        'caption' => $reel->caption,
                        'location' => $reel->location,
                        'likes_count' => $reel->likes_count,
                        'comments_count' => $reel->comments_count,
                        'shares_count' => $reel->shares_count,
                        'gifts_count' => $reel->gifts_count,
                        'bigups_count' => $reel->bigups_count,
                        'is_liked' => $reel->likes()->where('user_id', $currentUser->id)->exists(),
                        'created_at' => $reel->created_at?->toISOString(),
                    ];
                });
        }

        return response()->json([
            'is_following' => $isFollowing,
            'followers_count' => CreatorFollower::where('creator_id', $user->id)->count(),
            'message' => $isFollowing ? 'Creator followed' : 'Creator unfollowed',
            'reels' => $userReels,
        ]);
    }
}
