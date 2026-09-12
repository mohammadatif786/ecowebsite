<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChannel extends Model
{
    protected $fillable = [
        'icon',
        'name',
        'price',
        'unit',
        'locked',
    ];

    protected $casts = [
        'price' => 'float',
        'locked' => 'boolean',
    ];
}
