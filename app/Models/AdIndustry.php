<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdIndustry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'multiplier',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'float',
        'is_active' => 'boolean',
    ];
}
