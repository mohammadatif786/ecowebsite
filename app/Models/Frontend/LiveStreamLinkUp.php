<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class LiveStreamLinkUp extends Model
{
    protected $fillable = [
        'firebase_id',
        'channel_name',
        'type',
        'user_id',
        'status',
        'join',
    ];
}
