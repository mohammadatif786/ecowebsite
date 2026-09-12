<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerritoryTier extends Model
{
    protected $fillable = [
        'label',
        'name',
        'price_min',
        'price_max',
        'multiplier',
    ];

    protected $casts = [
        'price_min' => 'float',
        'price_max' => 'float',
        'multiplier' => 'float',
    ];
}
