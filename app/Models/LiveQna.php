<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQna extends Model
{
    protected $table = 'live_qna';
    protected $fillable = [
        'live_stream_gumlet_id',
        'user_id',
        'text',
        'answered',
    ];

    protected $casts = [
        'answered' => 'boolean',
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


