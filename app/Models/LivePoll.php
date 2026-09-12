<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LivePoll extends Model
{
    protected $fillable = [
        'live_stream_gumlet_id',
        'question',
        'active',
        'active_marker',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStreamGumlet::class, 'live_stream_gumlet_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(LivePollOption::class, 'live_poll_id');
    }
}

