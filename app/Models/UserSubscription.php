<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = [
        'amount',
        'subscription_id',
        'user_id',
        'status',
        'firebase_id'
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('firebase_id', 'like', '%' . $search . '%');
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription details.
     */
    public function subscription()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
