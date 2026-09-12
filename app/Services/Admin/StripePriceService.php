<?php

namespace App\Services\Admin;

use App\Enums\BillingCycle;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripePriceService
{
    public function __construct(private readonly StripeClient $stripe) {}

    /**
     * @throws ApiErrorException
     */
    public function createPriceForProduct(string $stripeProductId, float $amount, BillingCycle $billingCycle): string
    {
        $payload = [
            'product' => $stripeProductId,
            'currency' => 'usd',
            'unit_amount' => (int) round($amount * 100),
        ];

        if ($interval = $billingCycle->stripeInterval()) {
            $payload['recurring'] = ['interval' => $interval];
        }

        $price = $this->stripe->prices->create(
            $payload,
            ['idempotency_key' => 'price_create_' . Str::uuid()->toString()]
        );

        return $price->id;
    }
}
