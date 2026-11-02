<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "Follow" on Vibes and Reels: one user follows a creator.
 * One-way — it does not make the two users friends.
 */
class CreatorFollower extends Model
{
    protected $fillable = ['follower_id', 'creator_id'];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** IDs of the creators this user follows. */
    public static function followedIds(int $followerId): array
    {
        return self::query()->where('follower_id', $followerId)->pluck('creator_id')->map(fn ($id) => (int) $id)->all();
    }
}
