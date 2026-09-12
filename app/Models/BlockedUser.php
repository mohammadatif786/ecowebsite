<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    protected $fillable = [
        'user_id',
        'blocked_user_id', 
        'is_blocked',
        'reason',
        'note'
    ];

    protected $casts = [
        'is_blocked' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    // Check if a user is blocked by another user
    public static function isBlocked($userId, $blockedUserId)
    {
        return self::where('user_id', $userId)
            ->where('blocked_user_id', $blockedUserId)
            ->where('is_blocked', true)
            ->exists();
    }

    // Block a user
    public static function blockUser($userId, $blockedUserId,$reason,$note)
    {


        return self::updateOrCreate(
            ['user_id' => $userId, 'blocked_user_id' => $blockedUserId,'reason'=> $reason,'note'=> $note],
            ['is_blocked' => true],
        );
    }

    // Unblock a user
    public static function unblockUser($userId, $blockedUserId)
    {
        return self::where('user_id', $userId)
            ->where('blocked_user_id', $blockedUserId)
            ->delete();
    }
}
