<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'country',
        'tax_type',
        'tax',
        'country_label',
    ];

    protected $casts = [
        'tax' => 'decimal:2',
    ];
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('country', 'like', '%' . $search . '%');
        });
    }
}
