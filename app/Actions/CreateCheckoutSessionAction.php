<?php

namespace App\Actions;

use App\Actions\CheckoutSessionData;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Stripe\Checkout\Session;

class CreateCheckoutSessionAction
{
    public function __construct(
        private readonly StripeCheckoutService $stripeService,
    ) {}

    public function execute(User $user, CheckoutSessionData $data): Session
    {
        $this->stripeService->ensureStripeCustomer($user);

        return $this->stripeService->createCheckoutSession($user, $data);
    }
}
