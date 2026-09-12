<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveViewer extends Model
{
    protected $fillable = [
        'live_stream_gumlet_id',
        'user_id',
        'joined_at',
        'last_heartbeat',
        'left_at',
        'is_active'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_heartbeat' => 'datetime',
        'left_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function stream()
    {
        return $this->belongsTo(LiveStreamGumlet::class, 'live_stream_gumlet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active viewers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get viewers who haven't sent heartbeat recently
     */
    public function scopeStale($query, $minutes = 2)
    {
        return $query->where('last_heartbeat', '<', now()->subMinutes($minutes));
    }
}
