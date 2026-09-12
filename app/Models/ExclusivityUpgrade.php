<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExclusivityUpgrade extends Model
{
    protected $fillable = [
        'icon',
        'name',
        'pct',
    ];

    protected $casts = [
        'pct' => 'integer',
    ];
}
