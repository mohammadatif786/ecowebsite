<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class EMoney extends Model
{
    protected $fillable = [
        'charge_earned',
        'current_balance',
        'user_id',
        'firebase_id',
    ];
}
