<?php

namespace App\DTOs;

class BankWithdrawalDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly float $amount,
        public readonly int $bankIndex,
        public readonly ?string $bankName = null,
        public readonly ?string $accountNumber = null,
    ) {
    }
}
