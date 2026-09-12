<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        // 'firebase_id',
        'user_id',
        'number',
        'total',
        'country',
        'city',
        'state',
        'zipcode',
        'street_address',
        'status',
        'shipping_method',
        'payment_method',
        'subtotal_amount',
        'shipping_amount',
        'tax_amount',
        'discount_amount',
        'fee_amount',
        'fee_label',
        'process_fee_amount',
        'net_total'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('number', 'like', '%' . $search . '%');
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->number)) {
                $order->number = 'ORD-' . strtoupper(Str::random(10));
            }
        });
    }

    public function trackings()
    {
        return $this->hasMany(OrderTracking::class);
    }
}
