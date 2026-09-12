<?php

namespace App\Actions;

use App\DTOs\CoinRechargeDTO;
use App\Services\StripePaymentService;

class InitiateCoinRechargeAction
{
    public function __construct(
        protected StripePaymentService $stripeService
    ) {}

    public function execute(CoinRechargeDTO $dto): string
    {
        $session = $this->stripeService->createCoinCheckoutSession($dto);
        return $session->url;
    }
}
