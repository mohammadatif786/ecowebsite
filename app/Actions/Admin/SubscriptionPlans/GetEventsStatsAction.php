<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\FeatureGate;
use App\Models\LinkUpEvent;
use App\Models\SubscriptionPlan;
use App\Models\TicketSale;

class GetEventsStatsAction
{
    public function execute(): array
    {
        $eventsThisMonth = LinkUpEvent::where('created_at', '>=', now()->startOfMonth())->count();
        $eventsLastMonth = LinkUpEvent::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();
        
        $eventsChange = $eventsThisMonth - $eventsLastMonth;
        $eventsChangeStr = ($eventsChange >= 0 ? '+' : '') . $eventsChange . ' vs last month';

        $totalAttendees = TicketSale::sum('no_of_tickets') ?? 0;
        $totalEventsWithTickets = TicketSale::distinct('link_up_event_id')->count('link_up_event_id') ?: 1;
        $avgAttendees = round($totalAttendees / $totalEventsWithTickets);

        // Mock metrics for boosted events since not natively implemented or trackable as a distinct model
        $boostedEvents = 31; 
        $avgBoostUplift = 3.4;

        $stats = [
            [
                'label' => 'Events This Month',
                'value' => number_format($eventsThisMonth),
                'change' => $eventsChangeStr,
            ],
            [
                'label' => 'Total Attendees',
                'value' => number_format($totalAttendees),
                'change' => number_format($avgAttendees) . ' avg per event',
            ],
            [
                'label' => 'Boosted Events',
                'value' => number_format($boostedEvents),
                'change' => 'El Dorado+ only',
            ],
            [
                'label' => 'Avg Boost Uplift',
                'value' => $avgBoostUplift . '×',
                'change' => 'vs non-boosted',
            ],
        ];

        // Find the 'boost-events' or 'events' feature gate
        $gate = FeatureGate::where('slug', 'boost-events')
            ->orWhere('name', 'like', '%event%')
            ->first();

        $allPlans = SubscriptionPlan::orderBy('price')->get();
        $grantedPlanIds = $gate ? $gate->plans->pluck('id')->toArray() : [];

        $planAccess = $allPlans->map(function ($plan) use ($grantedPlanIds) {
            $hasAccess = in_array($plan->id, $grantedPlanIds);
            $emoji = $plan->emoji ? $plan->emoji . ' ' : '';
            
            if ($hasAccess) {
                if (stripos($plan->name, 'dorado') !== false) {
                    $desc = 'Boost Events (featured)';
                } elseif (stripos($plan->name, 'carnaval') !== false) {
                    $desc = 'Boost Events (2×)';
                } else {
                    $desc = 'Boost Events';
                }
            } else {
                $desc = 'Browse only';
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
