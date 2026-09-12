<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveFollower extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stream_id',
    ];

    // Relationship with the user who is following
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the stream being followed
    public function stream()
    {
        return $this->belongsTo(LiveStream::class);
    }

    // Get followers count for a stream
    public static function getFollowersCount($streamId)
    {
        return self::where('stream_id', $streamId)->count();
    }

    // Check if user is following a stream
    public static function isUserFollowing($userId, $streamId)
    {
        return self::where('user_id', $userId)
                   ->where('stream_id', $streamId)
                   ->exists();
    }

    // Get all followers for a stream
    public static function getStreamFollowers($streamId)
    {
        return self::where('stream_id', $streamId)
                   ->with('user')
                   ->get();
    }
}
