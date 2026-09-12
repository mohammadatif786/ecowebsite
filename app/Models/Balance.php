<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balances';
    protected $fillable = [
        'payable',
        'value',
        'value_pending',
        'value_on_hold',
        'currency',
    ];
}
