<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LiveStreamGumlet extends Model
{
    protected $hidden = ['stream_key', 'live_asset_id', 'live_video_source_id', 'stream_url', 'playback_url'];

    protected $fillable = [
        'title',
        'user_id',
        'status',
        'stream_key',
        'live_asset_id',
        'live_video_source_id',
        'resolution',
        'stream_url',
        'playback_url',
        'thumbnail',
        'visibility',
        'broadcast_type',
        'start_time',
        'host_heartbeat_at',
        'ended_at',
        'location',
        'cover_image',
        'base_resolution',
        'output_resolution',
        'downscale_filter',
        'subscription_rate',
        'like_count',
        'guests',
        'products',
        'is_paid',
        'paid_coins',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $stream): void {
            $stream->public_id ??= (string) Str::ulid();
        });
    }

    protected $casts = [
        'guests' => 'array',
        'products' => 'array',
        'start_time' => 'datetime',
        'host_heartbeat_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : 'https://via.placeholder.com/400';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function viewers()
    {
        return $this->hasMany(LiveViewer::class, 'live_stream_gumlet_id');
    }

    public function activeViewers()
    {
        return $this->viewers()->where('is_active', true);
    }

    public function getViewerCountAttribute()
    {
        return $this->activeViewers()->count();
    }

    public function gifts()
    {
        return $this->hasMany(LiveGift::class, 'live_stream_gumlet_id');
    }

    public function getGiftCountAttribute()
    {
        return $this->gifts()->sum('qty');
    }
}
