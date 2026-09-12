<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopFee extends Model
{
    protected $fillable = [
        'enabled',
        'fee_type',
        'label',
        'percent',
        'fixed',
        'currency',
        'min_fee',
        'max_fee',
        'disclaimer',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'percent' => 'decimal:2',
        'fixed' => 'decimal:2',
        'min_fee' => 'decimal:2',
        'max_fee' => 'decimal:2',
    ];
}
