<?php

namespace App\Services;

use App\Actions\CheckoutSessionData;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Stripe;

class StripeCheckoutService
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    /**
     * Ensure user has a Stripe customer record via Cashier.
     */
    public function ensureStripeCustomer(User $user): void
    {
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer([
                'email' => $user->email,
                'name'  => $user->name,
            ]);
        }
    }

    /**
     * Retrieve and validate a Stripe price.
     *
     * @throws ApiErrorException
     */
    public function retrievePrice(string $priceId): Price
    {
        $price = Price::retrieve($priceId);

        if (!$price || !$price->id) {
            throw new \RuntimeException('No valid price found for this product.');
        }

        return $price;
    }

    /**
     * Create a Stripe Checkout Session for a subscription.
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(User $user, CheckoutSessionData $data): Session
    {
        return Session::create([
            'customer'             => $user->stripe_id,
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price'    => $data->stripePriceId,
                'quantity' => 1,
            ]],
            'mode'              => 'subscription',
            'subscription_data' => [
                'metadata' => [
                    'user_id'  => $user->id,
                    'price_id' => $data->stripePriceId,
                    'plan_id'  => $data->planId,
                ],
            ],
            'success_url' => $data->successUrl,
            'cancel_url'  => $data->cancelUrl,
        ]);
    }

    /**
     * Retrieve a completed checkout session with expansions.
     *
     * @throws ApiErrorException
     */
    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return Session::retrieve([
            'id'     => $sessionId,
            'expand' => ['line_items', 'subscription'],
        ]);
    }
}
