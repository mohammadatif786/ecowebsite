<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'send_by',
        'user_id',
        'type',
        'context',
        'unread',
        'priority',
        'icon',
        'avatar',
        'metadata'
    ];

    protected $casts = [
        'unread' => 'boolean',
        'priority' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'send_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('unread', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriority($query)
    {
        return $query->where('priority', true);
    }
}
