<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\SubscribedPlan;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Logic to retrieve subscription plans
    public function getSubscriptionPlans()
    {
        $userId = auth()->id();
        $plans = SubscriptionPlan::where('status', true)->get();

        // Get all plan_ids the user has subscribed to
        $userSubscribedPlanIds = SubscribedPlan::where('user_id', $userId)
            ->pluck('plan_id')
            ->toArray();

        // Add is_subscribed to each plan
        $plans = $plans->map(function ($plan) use ($userSubscribedPlanIds) {
            $plan->is_subscribed = in_array($plan->stripe_price_id, $userSubscribedPlanIds);
            return $plan;
        });

        if ($plans->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No subscription plans found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $plans
        ]);
    }

    public function getSubscriptionStatus()
    {
        $userId = auth()->id();

        $hasActiveSubscription = SubscribedPlan::where('user_id', $userId)
            ->where('status', true)
            ->whereDate('end_date', '>=', now())
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'status' => false,
                'message' => 'No active subscription plans found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Active subscription found.'
        ]);
    }
}
