<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    protected $fillable = [
        'firebase_id',
        'channel_id',
        'token',
        'app_id',
        'status'
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('firebase_id', 'like', '%' . $search . '%');
        });
    }

    // Relationship with followers
    public function followers()
    {
        return $this->hasMany(LiveFollower::class, 'stream_id');
    }

    // Get followers count
    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    // Check if current user is following this stream
    public function isUserFollowing($userId)
    {
        return $this->followers()->where('user_id', $userId)->exists();
    }

    // Follow a stream
    public function follow($userId)
    {
        if (!$this->isUserFollowing($userId)) {
            return $this->followers()->create(['user_id' => $userId]);
        }
        return false;
    }

    // Unfollow a stream
    public function unfollow($userId)
    {
        return $this->followers()->where('user_id', $userId)->delete();
    }
}
