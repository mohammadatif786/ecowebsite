<?php

namespace App\Actions;

class CheckoutSessionData
{
    public function __construct(
        public readonly string $stripePriceId,
        public readonly int    $planId,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
    ) {}

    public static function fromRequest(array $data, string $successUrl, string $cancelUrl): self
    {
        return new self(
            stripePriceId: $data['plan_price_id'],
            planId: $data['plan_id'],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );
    }
}
