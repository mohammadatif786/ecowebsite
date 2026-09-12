<?php

namespace App\DTOs;

class UserBankDetailsDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $paypalId,
        /** @var array<int, array<string, mixed>> */
        public readonly array $banks,
    ) {
    }
}
