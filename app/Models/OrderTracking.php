<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'note',
        'meta',
        'last_update',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_update' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
