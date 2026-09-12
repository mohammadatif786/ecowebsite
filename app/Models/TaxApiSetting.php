<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxApiSetting extends Model
{
    use HasFactory;

    protected $fillable = ['provider', 'end_point_url', 'api_key', 'is_active'];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];
}
