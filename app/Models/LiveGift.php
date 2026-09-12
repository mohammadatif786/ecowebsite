<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveGift extends Model
{
    protected $fillable = [
        'live_stream_gumlet_id',
        'sender_id',
        'receiver_id',
        'gift_id',
        'linkup_live_gift_id',
        'qty',
        'coins',
        'host_share_cents',
        'idempotency_key',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStreamGumlet::class, 'live_stream_gumlet_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
