<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSurgeOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'multiplier',
        'description',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'float',
        'is_active' => 'boolean',
    ];
}
