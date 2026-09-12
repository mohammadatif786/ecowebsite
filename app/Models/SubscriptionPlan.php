<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'firebase_id',
        'stripe_product_id',
        'stripe_price_id',
        'name',
        'emoji',
        'tagline',
        'duration_days',
        'price',
        'billing_cycle',
        'description',
        'features',
        'perks',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'perks' => 'array',
        'billing_cycle' => BillingCycle::class,
        'status' => SubscriptionPlanStatus::class,
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function (Builder $query, string $search) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where('name', 'like', '%' . $escaped . '%');
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionPlanStatus::Active);
    }

    // No auth() coupling here — works in eager loading, queues, and for any user.
    public function subscriptions()
    {
        return $this->hasMany(SubscribedPlan::class, 'plan_id', 'stripe_price_id');
    }

    public function userSubscriptions(int $userId)
    {
        return $this->subscriptions()->where('user_id', $userId);
    }
}
