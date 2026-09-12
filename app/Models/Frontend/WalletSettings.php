<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class WalletSettings extends Model
{
    protected $casts = [
        'value' => 'array',
    ];

    protected $fillable = [
        'firebase_id',
        'value',
        'key',
    ];
}
