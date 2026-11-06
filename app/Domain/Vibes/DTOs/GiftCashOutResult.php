<?php

namespace App\Domain\Vibes\DTOs;

final readonly class GiftCashOutResult
{
    public function __construct(
        public int $coinsConverted,
        public float $payout,
        public float $walletBalance,
        public int $remainingCashableCoins,
        public int $coinBalance,
    ) {}

    public function toArray(): array
    {
        return [
            'coins_converted' => $this->coinsConverted,
            'payout' => $this->payout,
            'new_wallet_balance' => $this->walletBalance,
            'remaining_received_coins' => $this->remainingCashableCoins,
            'user_coins' => $this->coinBalance,
        ];
    }
}
