<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gift_id',
        'quantity',
        'total_coins',
        'payment_method',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
