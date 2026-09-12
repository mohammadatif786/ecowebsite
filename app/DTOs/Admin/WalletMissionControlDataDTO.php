<?php

namespace App\DTOs\Admin;

class WalletMissionControlDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $walletMovements,
        public array $walletUsers = [],
        public array $walletUserStats = [],
        public array $walletCountryBalances = [],
        public array $currencyExposure = [],
        public array $walletBalanceSummary = [],
        public array $settlements = [],
        public array $settlementSummary = [],
        public array $asueCircles = [],
        public array $asueSummary = [],
        public array $payoutQueue = [],
        public array $payoutQueueSummary = []
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'walletMovements' => $this->walletMovements,
            'walletUsers' => $this->walletUsers,
            'walletUserStats' => $this->walletUserStats,
            'walletCountryBalances' => $this->walletCountryBalances,
            'currencyExposure' => $this->currencyExposure,
            'walletBalanceSummary' => $this->walletBalanceSummary,
            'settlements' => $this->settlements,
            'settlementSummary' => $this->settlementSummary,
            'asueCircles' => $this->asueCircles,
            'asueSummary' => $this->asueSummary,
            'payoutQueue' => $this->payoutQueue,
            'payoutQueueSummary' => $this->payoutQueueSummary,
        ];
    }
}
