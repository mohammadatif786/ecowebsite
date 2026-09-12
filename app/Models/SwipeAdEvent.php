<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwipeAdEvent extends Model
{
    protected $fillable = [
        'advertisement_id',
        'user_id',
        'event_type',   // impression | click | swipe_left
        'ip_address',
        'user_agent',
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
