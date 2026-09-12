<?php

namespace App\Http\Controllers\Frontend;

use Stripe\Price;
use Stripe\Stripe;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Laravel\Cashier\Subscription;

class SubscriptionController extends Controller
{

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function index()
    {
        $user = auth()->user();
        $allpackages = SubscriptionPlan::all();
        $activeSubscriptions = Subscription::where('user_id', $user->id)
            ->where('stripe_status', 'paid')
            ->pluck('plan_id') // only get plan_ids
            ->toArray();
        return Inertia::render('User/Subscription/Index', [
            'plans' => $allpackages,
            'activePlanIds' => $activeSubscriptions
        ]);
    }

    public function redircetCheckout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Fetch the Stripe Price based on the given ID
        $price = Price::retrieve($request->plan_price_id);

        if (!$price || !$price->id) {
            return response()->json(['error' => 'No valid price found for this product.'], 400);
        }

        // Ensure the user has a Stripe customer ID
        if (!$user->stripe_id) {
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_id = $stripeCustomer->id;
            $user->save();
        }

        $mode = $price->type === 'recurring' ? 'subscription' : 'payment';

        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $price->id, // Use Stripe price ID for subscription/payment
                'quantity' => 1,
            ]],
            'mode' => $mode, // Set mode dynamically based on price type
            'success_url' => route('frontend.subscription.success') . '?session_id={CHECKOUT_SESSION_ID}' . '&user_id=' . $user->id .
                '&plan_id=' . $request->plan_id,
            'cancel_url' => route('frontend.subscription.cancel'),
        ];

        if ($mode === 'subscription') {
            $sessionData['subscription_data'] = [
                'metadata' => [
                    'user_id' => $user->id,
                    'price_id' => $price->id,
                    'plan_id' => $request->plan_id,
                ]
            ];
        } else {
            $sessionData['payment_intent_data'] = [
                'metadata' => [
                    'user_id' => $user->id,
                    'price_id' => $price->id,
                    'plan_id' => $request->plan_id,
                ]
            ];
        }

        // Create a payment session
        $checkoutSession = \Stripe\Checkout\Session::create($sessionData);

        // Return the checkout URL to the frontend
        return Inertia::location($checkoutSession->url);
    }

    public function subscriptionSuccess(Request $request)
    {
        $session_id = $request->session_id;
        $plan_id = $request->plan_id;
        $session_data = null;
        $error = false;
        $api_error = '';
        $user_id = $request->user_id;
        $amount = 0;

        // Fetch the Checkout Session
        try {
            $checkout_session = \Stripe\Checkout\Session::retrieve([
                'id' => $session_id,
                'expand' => ['line_items', 'subscription', 'payment_intent']
            ]);
            $session_data = $checkout_session;
            $line_item = $checkout_session->line_items->data[0];
            $amount = $line_item->price->unit_amount / 100;
        } catch (\Exception $e) {
            $error = true;
            $api_error = $e->getMessage();
            logger('Stripe API Error: ' . $api_error);
        }

        if ($error) {
            return redirect('/subscription-error')->with('error', $api_error);
        }

        // Extract necessary data from the session
        $payment_status = $session_data->payment_status; // Payment status

        if ($checkout_session->mode === 'subscription') {
            $stripe_subscription = $checkout_session->subscription;
            $stripe_id = $stripe_subscription->id;
            $plan_id = $stripe_subscription->metadata->plan_id ?? $request->plan_id;
            $end_date = now()->addMonth(); // Example: Subscription ends after 1 month
        } else {
            $payment_intent = $checkout_session->payment_intent;
            $stripe_id = $payment_intent ? $payment_intent->id : $session_id;
            $plan_id = ($payment_intent && isset($payment_intent->metadata->plan_id)) 
                ? $payment_intent->metadata->plan_id 
                : $request->plan_id;
            $end_date = null; // No end date for one-time payments
        }

        // Store the data in the database
        Subscription::create([
            'user_id' => $user_id,
            'type' => 'subscription',
            'quantity' => 1,
            'stripe_id' => $stripe_id,
            'stripe_price' => $amount,
            'stripe_status' => $payment_status,
            'plan_id' => $plan_id,
            'ends_at' => $end_date,
        ]);

        return Inertia::render('User/Subscription/Success');
    }

    public function subscriptionCancel()
    {
        // Handle the cancellation logic here
        // For example, you can redirect to a cancellation page or show a message
        return Inertia::render('User/Subscription/Index');
    }
}
