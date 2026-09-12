<?php

namespace App\Services;

use Stripe\StripeClient;
use App\DTOs\WalletRechargeDTO;
use App\DTOs\CoinRechargeDTO;

class StripePaymentService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a checkout session for wallet recharge
     * @param WalletRechargeDTO $data
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession(WalletRechargeDTO $data): \Stripe\Checkout\Session
    {
        return $this->stripe->checkout->sessions->create([
            'client_reference_id' => $data->userId,
            'metadata' => [
                'type' => 'wallet_recharge',
                'user_id' => $data->userId,
                'amount_cents' => $data->amount * 100,
            ],
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Wallet Recharge'],
                    'unit_amount' => $data->amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->successUrl($data->successRoute, $data->redirectTo),
            'cancel_url' => $this->cancelUrl($data->redirectTo),
        ]);
    }

    /**
     * Retrieve a checkout session
     * @param string $sessionId
     * @return \Stripe\Checkout\Session
     */
    public function retrieveSession(string $sessionId): \Stripe\Checkout\Session
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Create a checkout session for coin purchase
     * @param CoinRechargeDTO $data
     * @return \Stripe\Checkout\Session
     */
    public function createCoinCheckoutSession(CoinRechargeDTO $data): \Stripe\Checkout\Session
    {
        return $this->stripe->checkout->sessions->create([
            'client_reference_id' => $data->userId,
            'metadata' => [
                'type' => 'coin_purchase',
                'user_id' => $data->userId,
                'coin_count' => $data->coinCount,
            ],
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $data->coinCount . ' LinkUp Coins'],
                    'unit_amount' => $data->priceUsd * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->successUrl($data->successRoute, $data->redirectTo),
            'cancel_url' => $this->cancelUrl($data->redirectTo),
        ]);
    }

    private function successUrl(string $routeName, ?string $redirectTo): string
    {
        $url = route($routeName) . '?session_id={CHECKOUT_SESSION_ID}';

        if ($redirectTo) {
            $url .= '&redirect_to=' . urlencode($redirectTo);
        }

        return $url;
    }

    private function cancelUrl(?string $redirectTo): string
    {
        if (! $redirectTo) {
            return route('frontend.subscription.cancel');
        }

        if (str_starts_with($redirectTo, 'http://') || str_starts_with($redirectTo, 'https://')) {
            return $redirectTo;
        }

        return url($redirectTo);
    }
}
