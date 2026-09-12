<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'firebase_id',
        'transaction_id',
        'method',
        'price',
        'currency',
        'payer_id',
        'payer',
        'type',
        'sku',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('transaction_id', 'like', '%' . $search . '%')
                ->orWhere('payer', 'like', '%' . $search . '%');
        });
    }
}
