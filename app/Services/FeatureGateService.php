<?php

namespace App\Services;

use App\Models\User;

class FeatureGateService
{
    public function allows(User $user, string $feature): bool
    {
        $subscription = $user->checkSubscribed;

        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;

        if (!$plan) {
            return false;
        }

        $features = $plan->features ?? [];
        $perks = $plan->perks ?? [];

        $capabilities = array_merge($features, $perks);

        return (bool) ($capabilities[$feature] ?? false);
    }
}
