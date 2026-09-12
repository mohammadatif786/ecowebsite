<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivePollOption extends Model
{
    protected $fillable = [
        'live_poll_id',
        'text',
        'votes',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(LivePoll::class, 'live_poll_id');
    }
}


