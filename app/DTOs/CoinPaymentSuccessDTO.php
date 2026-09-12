<?php

namespace App\DTOs;

class CoinPaymentSuccessDTO
{
    public function __construct(
        public readonly string $sessionId,
        public readonly int $userId,
        public readonly int $coinCount
    ) {}
}
