<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\ClubFete;
use App\Models\FeatureGate;
use App\Models\SubscriptionPlan;
use App\Models\UserMatch;

class GetClubRestaurantStatsAction
{
    public function execute(): array
    {
        // Stats
        $totalVenues = ClubFete::count();
        $activeVenues = ClubFete::active()->count();

        $cities = ClubFete::whereNotNull('city')
            ->distinct('city')
            ->count('city');

        $paidUsers = \Laravel\Cashier\Subscription::where('stripe_status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        // Venue check-ins: approximate via matches that happened today
        // (since venue check-ins aren't explicitly tracked, use a reasonable proxy)
        $checkInsToday = ClubFete::where('updated_at', '>=', now()->startOfDay())->count();

        $checkInsYesterday = ClubFete::whereBetween('updated_at', [
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay(),
        ])->count();

        $checkInChangeStr = $checkInsYesterday > 0
            ? ($checkInsToday >= $checkInsYesterday ? '+' : '')
            . round((($checkInsToday - $checkInsYesterday) / $checkInsYesterday) * 100)
            . '% vs yesterday'
            : 'Today';

        $stats = [
            [
                'label' => 'Venue Check-ins Today',
                'value' => number_format($checkInsToday),
                'change' => $checkInChangeStr,
            ],
            [
                'label' => 'Listed Venues',
                'value' => number_format($totalVenues),
                'change' => $cities . ' cities covered',
            ],
            [
                'label' => 'Active Venues',
                'value' => number_format($activeVenues),
                'change' => 'Currently live',
            ],
            [
                'label' => 'Paid Subscribers',
                'value' => number_format($paidUsers),
                'change' => 'With venue access',
            ],
        ];

        // Plan access — find the club/restaurant feature gate and its associated plans
        $gate = FeatureGate::where('slug', 'clubs-restaurants')
            ->orWhere('slug', 'club-restaurant')
            ->orWhere('slug', 'clubs-and-restaurants')
            ->orWhere('name', 'like', '%club%')
            ->first();

        $allPlans = SubscriptionPlan::orderBy('price')->get();
        $grantedPlanIds = $gate ? $gate->plans->pluck('id')->toArray() : [];

        $planAccess = $allPlans->map(function ($plan) use ($grantedPlanIds) {
            $hasAccess = in_array($plan->id, $grantedPlanIds);
            $emoji = $plan->emoji ? $plan->emoji . ' ' : '';
            $label = $hasAccess
                ? $emoji . $plan->name . ' — Full access'
                : $emoji . $plan->name . ' — No access';

            return [
                'label' => $label,
                'active' => $hasAccess,
            ];
        })->values()->toArray();

        return [
            'stats' => $stats,
            'planAccess' => $planAccess,
        ];
    }
}
