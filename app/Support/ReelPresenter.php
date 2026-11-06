<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserReel;
use Illuminate\Database\Eloquent\Builder;

/**
 * One shape for a reel, used by the Vibes page, the reel lists and a single reel, so the web
 * reels viewer (and U Vibe) always get the same fields.
 */
class ReelPresenter
{
    /** Active reels the viewer may see (blocks both ways), newest first, ready for present(). */
    public static function query(User $viewer): Builder
    {
        return UserReel::active()
            ->visibleTo($viewer)
            ->with('user:id,name,avatar,linkup_id,city,country')
            ->withViewerFlags($viewer)
            ->latest()
            ->latest('id');
    }

    public static function present(UserReel $reel, User $viewer): array
    {
        $mine = (int) $reel->user_id === (int) $viewer->id;

        return [
            'id' => $reel->id,
            'uid' => $reel->uid,
            'user_id' => $reel->user_id,
            'handle' => $mine ? 'Your Reel' : ($reel->user?->linkup_id ?? $reel->user?->name ?? 'User'),
            'avatar' => $reel->user?->avatar,
            'name' => $reel->user?->name ?? 'User',
            'type' => $reel->type,
            'file_path' => $reel->file_path,
            'thumbnail_path' => $reel->thumbnail_path,
            'caption' => $reel->caption,
            'location' => $reel->location,
            'likes_count' => (int) $reel->likes_count,
            'comments_count' => (int) $reel->comments_count,
            'shares_count' => (int) $reel->shares_count,
            'gifts_count' => (int) $reel->gifts_count,
            'bigups_count' => (int) $reel->bigups_count,
            'is_liked' => (bool) ($reel->viewer_liked ?? false),
            'is_saved' => (bool) ($reel->viewer_saved ?? false),
            'is_mine' => $mine,
            'allow_coin_gifts' => $reel->allow_coin_gifts === null || (bool) $reel->allow_coin_gifts,
            'created_at' => $reel->created_at?->toISOString(),
        ];
    }

    /**
     * The stories bar: one circle per creator the viewer follows (or is friends with) who posted
     * a reel in the last 24 hours, newest first. Tapping a circle opens that creator's reels.
     *
     * @param  list<int>  $creatorIds
     * @return list<array{user_id: int, handle: string, name: string, avatar: ?string, latest_at: ?string}>
     */
    public static function stories(User $viewer, array $creatorIds, int $limit = 20): array
    {
        if ($creatorIds === []) {
            return [];
        }

        $latest = UserReel::active()
            ->visibleTo($viewer)
            ->whereIn('user_id', $creatorIds)
            ->where('user_id', '!=', $viewer->id)
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('user_id')
            ->selectRaw('user_id, max(created_at) as latest_at')
            ->orderByDesc('latest_at')
            ->limit($limit)
            ->get();

        $users = User::query()->whereIn('id', $latest->pluck('user_id'))->get(['id', 'name', 'avatar', 'linkup_id'])->keyBy('id');

        return $latest->map(function ($row) use ($users) {
            $u = $users->get($row->user_id);

            return [
                'user_id' => (int) $row->user_id,
                'handle' => $u?->linkup_id ?? $u?->name ?? 'User',
                'name' => $u?->name ?? 'User',
                'avatar' => $u?->avatar,
                'latest_at' => $row->latest_at ? (string) $row->latest_at : null,
            ];
        })->filter(fn ($s) => $users->has($s['user_id']))->values()->all();
    }
}
