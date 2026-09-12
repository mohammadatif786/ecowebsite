<?php

namespace App\DTOs;


class CoinRechargeDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $priceUsd,
        public readonly int $coinCount,
        public readonly ?string $redirectTo = null,
        public readonly string $successRoute = 'frontend.oneTimePayCoin.success'
    ) {}
}
