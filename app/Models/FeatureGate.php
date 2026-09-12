<?php

namespace App\Models;

use App\Enums\GateLockType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class FeatureGate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'lock_type',
        'is_active',
    ];

    protected $casts = [
        'lock_type' => GateLockType::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $gate) {
            $gate->slug ??= Str::slug($gate->name);
        });
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(
            SubscriptionPlan::class
        );
    }
}
