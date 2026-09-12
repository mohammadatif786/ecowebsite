<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignType extends Model
{
    protected $fillable = [
        'icon',
        'name',
        'duration',
        'priceMin',
        'priceMax',
        'basePrice',
        'reach',
        'taxRate',
        'description',
        'is_enterprise',
    ];

    protected $casts = [
        'duration' => 'string',
        'priceMin' => 'float',
        'priceMax' => 'float',
        'basePrice' => 'float',
        'reach' => 'integer',
        'taxRate' => 'float',
        'is_enterprise' => 'boolean',
    ];
}
