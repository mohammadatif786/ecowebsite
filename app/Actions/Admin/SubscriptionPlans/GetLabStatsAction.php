<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\FeatureGate;
use App\Models\SubscriptionPlan;

class GetLabStatsAction
{
    public function execute(): array
    {
        // Mock metrics for Lab features since these are AI/deep analytics
        // not yet fully implemented natively with specific tables.
        $labActiveUsers = 298;
        $compatibilityReports = 4102;
        $labAssistedMatches = 1839;
        $personalityProfiles = 298;

        $stats = [
            [
                'label' => 'Lab Active Users',
                'value' => number_format($labActiveUsers),
                'change' => '99% of eligible users',
            ],
            [
                'label' => 'Compatibility Reports',
                'value' => number_format($compatibilityReports),
                'change' => 'Generated this month',
            ],
            [
                'label' => 'Lab-Assisted Matches',
                'value' => number_format($labAssistedMatches),
                'change' => '2.1× better match rate',
            ],
            [
                'label' => 'Personality Profiles',
                'value' => number_format($personalityProfiles),
                'change' => 'All El Dorado+',
            ],
        ];

        // Find the 'the-lab' or 'lab' feature gate
        $gate = FeatureGate::where('slug', 'the-lab')
            ->orWhere('name', 'like', '%lab%')
            ->first();

        $allPlans = SubscriptionPlan::orderBy('price')->get();
        $grantedPlanIds = $gate ? $gate->plans->pluck('id')->toArray() : [];

        $planAccess = $allPlans->map(function ($plan) use ($grantedPlanIds) {
            $hasAccess = in_array($plan->id, $grantedPlanIds);
            $emoji = $plan->emoji ? $plan->emoji . ' ' : '';
            
            if ($hasAccess) {
                $desc = 'Full AI access';
            } else {
                $desc = 'No access';
            }

            return [
                'label' => $emoji . $plan->name . ' — ' . $desc,
                'active' => $hasAccess,
            ];
        })->values()->toArray();

        return [
            'stats' => $stats,
            'planAccess' => $planAccess,
        ];
    }
}
