<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class GetSubscribedUsersAction
{
    public function execute(): array
    {
        $users = User::whereHas('subscribed', function ($q) {
            $q->where('stripe_status', 'paid');
        })->with(['subscribed' => function($q) {
            $q->where('stripe_status', 'paid');
        }])->get();

        $colors = [
            'linear-gradient(135deg,#ec4899,#a855f7)',
            'linear-gradient(135deg,#f59e0b,#d97706)',
            'linear-gradient(135deg,#22d3a4,#0ea5e9)',
            'linear-gradient(135deg,#38bdf8,#818cf8)',
            'linear-gradient(135deg,#6366f1,#8b5cf6)',
            'linear-gradient(135deg,#f97316,#fbbf24)'
        ];

        return $users->map(function($user, $index) use ($colors) {
            $initials = collect(explode(' ', $user->name))->map(function($n) { return substr($n, 0, 1); })->take(2)->join('');
            
            $sub = $user->subscribed->first();
            $planName = 'N/A';
            if ($sub) {
                $plan = SubscriptionPlan::find($sub->plan_id);
                if ($plan) {
                    $planName = ($plan->emoji ? $plan->emoji . ' ' : '') . $plan->name;
                }
            }

            return [
                'id' => $user->id,
                'initials' => strtoupper($initials),
                'name' => $user->name,
                'email' => $user->email,
                'avatarBg' => $colors[$index % count($colors)],
                'plan' => $planName,
                'planBg' => 'rgba(200,230,58,.15)',
                'planColor' => '#5e7c00',
                'location' => implode(', ', array_filter([$user->city, $user->country])) ?: 'Unknown',
                'joined' => $user->created_at->format('M j, Y'),
                'lastActive' => $user->last_active ? Carbon::parse($user->last_active)->diffForHumans() : 'N/A',
                'status' => $user->is_active === 0 ? 'Banned' : 'Active',
            ];
        })->toArray();
    }
}
