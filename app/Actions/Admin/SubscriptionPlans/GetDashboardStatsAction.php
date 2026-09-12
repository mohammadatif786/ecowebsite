<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\User;
use App\Models\UserMatch;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\DB;

class GetDashboardStatsAction
{
    public function execute(): array
    {
        $totalUsers = User::count();
        $usersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
        
        $paidSubscribers = Subscription::where('stripe_status', 'paid')
            ->distinct('user_id')
            ->count();
        $paidSubscribersLastMonth = Subscription::where('stripe_status', 'paid')
            ->where('created_at', '<', now()->subMonth())
            ->distinct('user_id')
            ->count();
        
        $paidSubscribersChange = $paidSubscribersLastMonth > 0 
            ? round((($paidSubscribers - $paidSubscribersLastMonth) / $paidSubscribersLastMonth) * 100, 1) 
            : 0;

        $monthlyRevenue = Subscription::where('stripe_status', 'paid')
            ->where('created_at', '>=', now()->subMonth())
            ->sum('stripe_price');

        $dailySwipes = UserMatch::where('created_at', '>=', now()->subDay())->count();
        $dailyMatches = UserMatch::where('created_at', '>=', now()->subDay())
            ->where('status', 'like')->count();
            
        $activeLiveStreams = User::where('is_live_streaming', true)->count();
        $marketplaceListings = Product::count();
        $marketplaceListingsThisWeek = Product::where('created_at', '>=', now()->subWeek())->count();
        
        $totalSubs = Subscription::count();
        $canceledSubs = Subscription::where('stripe_status', 'canceled')->count();
        $churnRate = $totalSubs > 0 ? round(($canceledSubs / $totalSubs) * 100, 1) : 0;

        $stats = [
            [
                'label' => 'Total Users',
                'value' => number_format($totalUsers),
                'change' => '+'.$usersThisWeek.' this week',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            ],
            [
                'label' => 'Paid Subscribers',
                'value' => number_format($paidSubscribers),
                'change' => ($paidSubscribersChange >= 0 ? '+' : '').$paidSubscribersChange.'% this month',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            ],
            [
                'label' => 'Monthly Revenue',
                'value' => '$'.number_format($monthlyRevenue, 2),
                'change' => 'This month',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            ],
            [
                'label' => 'Daily Swipes',
                'value' => number_format($dailySwipes),
                'change' => 'Last 24 hours',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
            ],
            [
                'label' => 'Daily Matches',
                'value' => number_format($dailyMatches),
                'change' => 'Last 24 hours',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>',
            ],
            [
                'label' => 'Active Live Streams',
                'value' => number_format($activeLiveStreams),
                'change' => 'Right now',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
            ],
            [
                'label' => 'Marketplace Listings',
                'value' => number_format($marketplaceListings),
                'change' => '+'.$marketplaceListingsThisWeek.' this week',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>',
            ],
            [
                'label' => 'Churn Rate',
                'value' => $churnRate.'%',
                'change' => 'Overall',
                'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6 6"/></svg>',
            ],
        ];

        // Plan distribution
        $distribution = Subscription::select('plan_id', DB::raw('count(*) as count'))
            ->where('stripe_status', 'paid')
            ->groupBy('plan_id')
            ->get();
            
        $planIds = $distribution->pluck('plan_id')->filter();
        $plans = SubscriptionPlan::whereIn('id', $planIds)->get()->keyBy('id');
        
        $planDistribution = [];
        $colors = [
            'linear-gradient(90deg,#38bdf8,#818cf8)',
            'linear-gradient(90deg,#f59e0b,#f97316)',
            'linear-gradient(90deg,#C8E63A,#a8c420)',
            'linear-gradient(90deg,#f59e0b,#fbbf24)',
            'linear-gradient(90deg,#ec4899,#a855f7)'
        ];
        
        $totalSubscriptions = $distribution->sum('count');
        $i = 0;
        foreach($distribution as $dist) {
            $plan = $plans->get($dist->plan_id);
            if ($plan) {
                $width = $totalSubscriptions > 0 ? round(($dist->count / $totalSubscriptions) * 100) : 0;
                $planDistribution[] = [
                    'name' => ($plan->emoji ? $plan->emoji . ' ' : '') . $plan->name . ' $' . $plan->price,
                    'count' => $dist->count . ' subs',
                    'width' => $width . '%',
                    'color' => $colors[$i % count($colors)],
                ];
                $i++;
            }
        }

        // Recent Signups
        $recentSignupsData = Subscription::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $recentSignups = [];
        $i = 0;
        foreach($recentSignupsData as $sub) {
            $plan = SubscriptionPlan::find($sub->plan_id);
            $user = $sub->user;
            if ($user && $plan) {
                $initials = collect(explode(' ', $user->name))->map(function($n) { return substr($n, 0, 1); })->take(2)->join('');
                $recentSignups[] = [
                    'initials' => strtoupper($initials),
                    'name' => $user->name,
                    'joinedAt' => $sub->created_at->diffForHumans(),
                    'plan' => ($plan->emoji ? $plan->emoji . ' ' : '') . $plan->name,
                    'avatarBg' => $colors[$i % count($colors)],
                    'planBg' => 'rgba(200,230,58,.15)',
                    'planColor' => '#5e7c00'
                ];
                $i++;
            }
        }

        return [
            'stats' => $stats,
            'planDistribution' => $planDistribution,
            'recentSignups' => $recentSignups,
        ];
    }
}
