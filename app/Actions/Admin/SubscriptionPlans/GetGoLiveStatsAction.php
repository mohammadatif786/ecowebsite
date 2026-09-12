<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\FeatureGate;
use App\Models\LiveStreamGumlet;
use App\Models\SubscriptionPlan;
use App\Models\User;

class GetGoLiveStatsAction
{
    public function execute(): array
    {
        // Stats
        $activeStreams = LiveStreamGumlet::where('status', 'live')->count();
        $totalActiveViewers = LiveStreamGumlet::where('status', 'live')
            ->withCount(['activeViewers'])
            ->get()
            ->sum('active_viewers_count');

        $streamsThisMonth = LiveStreamGumlet::where('created_at', '>=', now()->startOfMonth())->count();
        $streamsLastMonth = LiveStreamGumlet::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();
        $monthlyChangeStr = $streamsLastMonth > 0
            ? ($streamsThisMonth >= $streamsLastMonth ? '+' : '')
              . round((($streamsThisMonth - $streamsLastMonth) / $streamsLastMonth) * 100)
              . '% vs last month'
            : 'This month';

        $uniqueStreamers = LiveStreamGumlet::where('created_at', '>=', now()->startOfMonth())
            ->distinct('user_id')
            ->count('user_id');

        $paidUsers = \Laravel\Cashier\Subscription::where('stripe_status', 'paid')
            ->distinct('user_id')
            ->count('user_id');
        $streamerPct = $paidUsers > 0 ? round(($uniqueStreamers / $paidUsers) * 100) : 0;

        // Avg viewers per stream this month
        $monthStreams = LiveStreamGumlet::where('created_at', '>=', now()->startOfMonth())
            ->withCount('viewers')
            ->get();
        $avgViewers = $monthStreams->count() > 0
            ? round($monthStreams->avg('viewers_count'), 1)
            : 0;

        $stats = [
            [
                'label' => 'Active Streams Now',
                'value' => (string) $activeStreams,
                'change' => $totalActiveViewers . ' viewers total',
            ],
            [
                'label' => 'Streams This Month',
                'value' => number_format($streamsThisMonth),
                'change' => $monthlyChangeStr,
            ],
            [
                'label' => 'Unique Streamers',
                'value' => number_format($uniqueStreamers),
                'change' => $streamerPct . '% of paid users',
            ],
            [
                'label' => 'Avg Viewers / Stream',
                'value' => (string) $avgViewers,
                'change' => 'This month',
            ],
        ];

        // Plan access — find the "go-live" feature gate and its associated plans
        $gate = FeatureGate::where('slug', 'go-live')
            ->orWhere('slug', 'live-stream')
            ->orWhere('name', 'like', '%live%')
            ->first();

        $allPlans = SubscriptionPlan::orderBy('price')->get();
        $grantedPlanIds = $gate ? $gate->plans->pluck('id')->toArray() : [];

        $planAccess = $allPlans->map(function ($plan) use ($grantedPlanIds) {
            $hasAccess = in_array($plan->id, $grantedPlanIds);
            $emoji = $plan->emoji ? $plan->emoji . ' ' : '';
            $label = $hasAccess
                ? $emoji . $plan->name . ' — Standard stream'
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
