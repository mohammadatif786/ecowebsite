<?php

namespace App\DTOs;

class WalletPaymentSuccessDTO
{
    public function __construct(
        public readonly string $sessionId
    ) {}
}
