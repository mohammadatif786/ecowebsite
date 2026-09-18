<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'status',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'shares_count' => 'integer',
            'gifts_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }

    public function comments()
    {
        return $this->morphMany(VibeComment::class, 'commentable');
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
                });
            });
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
}
