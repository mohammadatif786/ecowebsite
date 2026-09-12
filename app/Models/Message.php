<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    use BroadcastsEvents;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'content',
        'replied_to',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who received the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereHas('sender', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('receiver', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        });
    }
}
