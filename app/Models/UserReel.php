<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReel extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'user_id',
        'type',
        'file_path',
        'thumbnail_path',
        'caption',
        'location',
        'likes_count',
        'comments_count',
        'shares_count',
        'gifts_count',
        'bigups_count',
        'status',
        'is_uvibe',
        'campus',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'shares_count' => 'integer',
            'gifts_count' => 'integer',
            'bigups_count' => 'integer',
            'is_uvibe' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(ReelLike::class);
    }

    public function comments()
    {
        return $this->hasMany(ReelComment::class);
    }

    public function shares()
    {
        return $this->hasMany(ReelShare::class);
    }

    public function saves()
    {
        return $this->hasMany(ReelSave::class);
    }

    public function authUserLike()
    {
        return $this->hasOne(ReelLike::class)->where('user_id', auth()->id());
    }

    public function authUserSave()
    {
        return $this->hasOne(ReelSave::class)->where('user_id', auth()->id());
    }

    public function getIsLikedAttribute()
    {
        return $this->authUserLike()->exists();
    }

    public function getIsSavedAttribute()
    {
        return $this->authUserSave()->exists();
    }

    public function scopeFromFollowedUsers($query, $userId)
    {
        return $query->whereHas('user', function ($q) use ($userId) {
            $q->where(function ($subQuery) use ($userId) {
                $subQuery->whereIn('id', function ($innerQuery) use ($userId) {
                    $innerQuery->select('receiver_id')
                        ->from('friend_requests')
                        ->where('user_id', $userId)
                        ->where('status', 1);
                })->orWhereIn('id', function ($innerQuery) use ($userId) {
                    $innerQuery->select('user_id')
                        ->from('friend_requests')
                        ->where('receiver_id', $userId)
                        ->where('status', 1);
                })->orWhereIn('id', function ($innerQuery) use ($userId) {
                    // Creators the user follows on Vibes/Reels.
                    $innerQuery->select('creator_id')
                        ->from('creator_followers')
                        ->where('follower_id', $userId);
                });
            });
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Reels the viewer may see: nothing from someone who blocked them or whom they blocked. */
    public function scopeVisibleTo($query, ?User $viewer)
    {
        if (! $viewer) {
            return $query;
        }
        $id = $viewer->getKey();

        return $query
            ->whereNotIn('user_id', BlockedUser::query()->where('user_id', $id)->where('is_blocked', true)->select('blocked_user_id'))
            ->whereNotIn('user_id', BlockedUser::query()->where('blocked_user_id', $id)->where('is_blocked', true)->select('user_id'));
    }

    /** One loaded reel: active (or the viewer's own) and not hidden by a block. */
    public function isVisibleTo(?User $viewer): bool
    {
        if ($viewer && (int) $this->user_id === (int) $viewer->getKey()) {
            return true;
        }

        return $this->status === 'active'
            && self::query()->whereKey($this->getKey())->visibleTo($viewer)->exists();
    }

    /** Liked / saved flags for the viewer, loaded with the list (no query per reel). */
    public function scopeWithViewerFlags($query, User $viewer)
    {
        return $query->withExists([
            'likes as viewer_liked' => fn ($q) => $q->where('user_id', $viewer->getKey()),
            'saves as viewer_saved' => fn ($q) => $q->where('user_id', $viewer->getKey()),
        ]);
    }

    protected function getFilePathAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    protected function getThumbnailPathAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function incrementLikesCount()
    {
        $this->increment('likes_count');
    }

    public function decrementLikesCount()
    {
        $this->decrement('likes_count');
    }

    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }

    public function incrementSharesCount()
    {
        $this->increment('shares_count');
    }

    public function incrementGiftsCount()
    {
        $this->increment('gifts_count');
    }

    public function incrementBigupsCount()
    {
        $this->increment('bigups_count');
    }
}
