<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkupLiveGift extends Model
{
    protected $fillable = [
        'name',
        'emoji',
        'coins',
        'active',
    ];

    protected $casts = [
        'coins' => 'integer',
    ];
}


