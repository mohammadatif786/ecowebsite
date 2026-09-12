<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveComment extends Model
{
    protected $fillable = [
        'live_stream_gumlet_id',
        'user_id',
        'text',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStreamGumlet::class, 'live_stream_gumlet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


