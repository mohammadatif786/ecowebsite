<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\CreatorFollower;
use App\Models\Frontend\FriendRequest;
use App\Models\GiftCoins;
use App\Models\User;
use App\Models\UserReel;
use App\Models\Vibe;
use App\Support\VibeFeedPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Finding things on Vibes: trending hashtags, the search window (people + tags) and a
 * creator's profile (stats, mutuals, top supporters, shop, posts).
 */
class VibeExploreController extends Controller
{
    private const POSTS_PAGE_SIZE = 18;

    /** `GET vibes/trending-tags` → { tags: [{tag:'#fete', count}] } — last 30 days, else all time. */
    public function trendingTags(Request $request): JsonResponse
    {
        $top = fn (?int $days) => DB::table('vibe_hashtags')
            ->whereIn('vibe_id', VibeFeedPresenter::base($request->user())->select('vibes.id'))
            ->when($days, fn ($q) => $q->where('created_at', '>=', now()->subDays($days)))
            ->select('tag', DB::raw('count(*) as uses'))
            ->groupBy('tag')
            ->orderByDesc('uses')
            ->orderBy('tag')
            ->limit(10)
            ->get();

        $tags = $top(30);
        if ($tags->isEmpty()) {
            $tags = $top(null);
        }

        return response()->json([
            'tags' => $tags->map(fn ($t) => ['tag' => '#'.$t->tag, 'count' => (int) $t->uses])->values(),
        ]);
    }

    /**
     * `GET vibes/search?q=` → { q, people:[…], tags:[…] }. People by name, @handle, city or
     * country (never blocked accounts). With no `q`, suggests creators to follow.
     */
    public function search(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:80']]);
        $viewer = $request->user();
        $q = trim((string) ($data['q'] ?? ''));
        $handle = ltrim($q, '@#');
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $handle).'%';
        $following = CreatorFollower::followedIds($viewer->id);

        $people = User::query()
            ->whereKeyNot($viewer->id)
            ->where(fn ($w) => $w->whereNull('status')->orWhere('status', '!=', 0))
            ->whereNotIn('id', $this->blockedIds($viewer))
            ->when($handle !== '', fn ($w) => $w->where(fn ($m) => $m
                ->where('name', 'like', $like)
                ->orWhere('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('linkup_id', 'like', $like)
                ->orWhere('new_city', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('new_country', 'like', $like)
                ->orWhere('country', 'like', $like)))
            ->when($handle === '', fn ($w) => $w->where(fn ($m) => $m->whereHas('vibes')->orWhereHas('reels')))
            ->withCount(['vibes', 'reels'])
            ->orderByDesc('vibes_count')
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'uid', 'name', 'first_name', 'last_name', 'linkup_id', 'avatar', 'city', 'new_city', 'country', 'new_country']);

        $followers = CreatorFollower::query()->whereIn('creator_id', $people->pluck('id'))
            ->select('creator_id', DB::raw('count(*) as total'))->groupBy('creator_id')->pluck('total', 'creator_id');

        $tags = $handle === '' ? collect() : DB::table('vibe_hashtags')
            ->where('tag', 'like', strtolower(str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $handle)).'%')
            ->select('tag', DB::raw('count(*) as uses'))
            ->groupBy('tag')->orderByDesc('uses')->limit(8)->get()
            ->map(fn ($t) => ['tag' => '#'.$t->tag, 'count' => (int) $t->uses]);

        return response()->json([
            'q' => $q,
            'people' => $people->map(fn (User $u) => [
                'user_id' => $u->id,
                'uid' => $u->uid,
                'name' => $this->displayName($u),
                'handle' => $u->linkup_id ?: $this->displayName($u),
                'avatar' => $u->avatar,
                'location' => $this->location($u),
                'posts' => (int) $u->vibes_count + (int) $u->reels_count,
                'followers' => (int) ($followers[$u->id] ?? 0),
                'is_following' => in_array((int) $u->id, $following, true),
            ])->values(),
            'tags' => $tags->values(),
        ]);
    }

    /**
     * `GET vibes/creators/{user}?page=` → a creator's Vibes profile: who they are, follow / LinkUp
     * state, stats, mutual followers, top supporters, what they sell and their posts.
     */
    public function creator(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();
        abort_if(in_array((int) $user->id, $this->blockedIds($viewer), true), 404, 'This profile isn’t available.');

        $page = max(1, (int) $request->query('page', 1));
        $postsQuery = VibeFeedPresenter::query($viewer)->where('created_by', $user->id);
        $posts = (clone $postsQuery)->latest()
            ->skip(($page - 1) * self::POSTS_PAGE_SIZE)->take(self::POSTS_PAGE_SIZE + 1)->get();
        $hasMore = $posts->count() > self::POSTS_PAGE_SIZE;
        $posts = $posts->take(self::POSTS_PAGE_SIZE);

        $postIds = VibeFeedPresenter::base($viewer)->where('created_by', $user->id)->pluck('vibes.id');
        if ($page > 1) {
            return response()->json([
                'posts' => $posts->map(fn (Vibe $v) => VibeFeedPresenter::present($v))->values(),
                'page' => $page,
                'has_more' => $hasMore,
            ]);
        }

        $reels = UserReel::active()->where('user_id', $user->id);
        $likes = (int) Vibe::whereIn('id', $postIds)->sum('likes_count') + (int) (clone $reels)->sum('likes_count');
        $bigUpCoins = (int) GiftCoins::where('recieved_id', $user->id)
            ->where(fn ($q) => $q->whereNotNull('vibe_id')->orWhereNotNull('reel_id'))->sum('coins');

        $viewerFollows = CreatorFollower::followedIds($viewer->id);
        $mutualIds = CreatorFollower::where('creator_id', $user->id)->whereIn('follower_id', $viewerFollows)->pluck('follower_id');

        return response()->json([
            'creator' => [
                'user_id' => $user->id,
                'uid' => $user->uid,
                'name' => $this->displayName($user),
                'handle' => $user->linkup_id ?: $this->displayName($user),
                'avatar' => $user->avatar,
                'location' => $this->location($user),
                'about' => $user->about_me,
                'is_self' => $user->id === $viewer->id,
                'is_following' => in_array((int) $user->id, $viewerFollows, true),
                'follows_you' => CreatorFollower::where('follower_id', $user->id)->where('creator_id', $viewer->id)->exists(),
                'linkup_status' => $this->linkupStatus($viewer, $user),
                'on_linkup' => ! (bool) $user->hide_profile,
            ],
            // Their LinkUp (dating) card, for "View LinkUp Profile" — only when they show it.
            'linkup_profile' => $user->hide_profile || $user->id === $viewer->id ? null : [
                'id' => $user->id,
                'uid' => $user->uid,
                'name' => $this->displayName($user),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => $user->avatar,
                'age' => $user->show_age === false || $user->show_age === 0 ? null : $user->age,
                'gender' => $user->gender,
                'country' => $user->new_country ?: $user->country,
                'city' => $user->new_city ?: $user->city,
                'about_me' => $user->about_me,
                'more_photos' => $user->more_photos,
                'interests' => $user->interests,
            ],
            'stats' => [
                'posts' => $postIds->count(),
                'reels' => (clone $reels)->count(),
                'followers' => CreatorFollower::where('creator_id', $user->id)->count(),
                'following' => CreatorFollower::where('follower_id', $user->id)->count(),
                'likes' => $likes,
                'bigup_coins' => $bigUpCoins,
            ],
            'mutuals' => [
                'count' => $mutualIds->count(),
                'people' => User::whereIn('id', $mutualIds->take(3))->get(['id', 'name', 'first_name', 'last_name', 'linkup_id', 'avatar'])
                    ->map(fn (User $u) => ['user_id' => $u->id, 'name' => $this->displayName($u), 'avatar' => $u->avatar])->values(),
            ],
            'top_supporters' => $this->topSupporters($user, $viewer),
            'shop' => $this->shop($user, $viewer),
            'posts' => $posts->map(fn (Vibe $v) => VibeFeedPresenter::present($v))->values(),
            'page' => 1,
            'has_more' => $hasMore,
        ]);
    }

    /** People who sent this creator the most coins (Big Ups and gifts), blocked accounts left out. */
    private function topSupporters(User $creator, User $viewer): array
    {
        $rows = GiftCoins::query()
            ->where('recieved_id', $creator->id)
            ->whereNotIn('sender_id', $this->blockedIds($viewer))
            ->select('sender_id', DB::raw('sum(coins) as total'))
            ->groupBy('sender_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $senders = User::whereIn('id', $rows->pluck('sender_id'))->get(['id', 'name', 'first_name', 'last_name', 'linkup_id', 'avatar'])->keyBy('id');

        return $rows->filter(fn ($r) => $senders->has($r->sender_id))->map(fn ($r) => [
            'user_id' => (int) $r->sender_id,
            'name' => $this->displayName($senders[$r->sender_id]),
            'avatar' => $senders[$r->sender_id]->avatar,
            'coins' => (int) $r->total,
        ])->values()->all();
    }

    /**
     * "Shop @creator": the items and events tagged in the creator's posts (newest first, each
     * once). Each keeps its post's `vibe_id`, so buying from here goes through that post.
     */
    private function shop(User $creator, User $viewer): array
    {
        return VibeFeedPresenter::query($viewer)
            ->where('created_by', $creator->id)
            ->where(fn ($q) => $q->whereHas('products', fn ($p) => $p->where('products.status', true))->orWhereHas('events'))
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (Vibe $v) => VibeFeedPresenter::tag($v))
            ->filter()
            ->unique(fn ($t) => $t['kind'].':'.$t['id'])
            ->take(10)
            ->values()
            ->all();
    }

    /** none | sent | received | friends — the LinkUp (friend) connection, not the Vibes follow. */
    private function linkupStatus(User $viewer, User $other): string
    {
        if ($viewer->id === $other->id) {
            return 'self';
        }

        $rows = FriendRequest::query()->where(fn (Builder $q) => $q
            ->where(fn ($a) => $a->where('user_id', $viewer->id)->where('receiver_id', $other->id))
            ->orWhere(fn ($b) => $b->where('user_id', $other->id)->where('receiver_id', $viewer->id)))
            ->get(['user_id', 'status']);

        if ($rows->contains(fn ($r) => (int) $r->status === 1)) {
            return 'friends';
        }
        $pending = $rows->first(fn ($r) => (int) $r->status === 0);

        return match (true) {
            $pending && (int) $pending->user_id === (int) $viewer->id => 'sent',
            (bool) $pending => 'received',
            default => 'none',
        };
    }

    /** Users the viewer blocked, and users who blocked the viewer. */
    private function blockedIds(User $viewer): array
    {
        return BlockedUser::query()->where('is_blocked', true)
            ->where(fn ($q) => $q->where('user_id', $viewer->id)->orWhere('blocked_user_id', $viewer->id))
            ->get(['user_id', 'blocked_user_id'])
            ->map(fn ($b) => (int) ((int) $b->user_id === (int) $viewer->id ? $b->blocked_user_id : $b->user_id))
            ->unique()->values()->all();
    }

    private function displayName(User $u): string
    {
        $name = trim((string) $u->name);
        if ($name === '') {
            $name = trim($u->first_name.' '.$u->last_name);
        }

        return $name !== '' ? $name : ($u->linkup_id ?: 'User');
    }

    private function location(User $u): ?string
    {
        $parts = array_filter([$u->new_city ?: $u->city, $u->new_country ?: $u->country]);

        return $parts ? implode(', ', $parts) : null;
    }
}
