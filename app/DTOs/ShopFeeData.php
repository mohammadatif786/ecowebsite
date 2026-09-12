<?php

namespace App\DTOs;

class ShopFeeData
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $feeType,
        public readonly string $label,
        public readonly float $percent,
        public readonly float $fixed,
        public readonly string $currency,
        public readonly float $minFee,
        public readonly float $maxFee,
        public readonly ?string $disclaimer
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? true),
            feeType: $data['feeType'] ?? 'percent',
            label: $data['label'] ?? 'Marketplace Fee',
            percent: (float) ($data['percent'] ?? 0),
            fixed: (float) ($data['fixed'] ?? 0),
            currency: $data['currency'] ?? 'USD',
            minFee: (float) ($data['minFee'] ?? 0),
            maxFee: (float) ($data['maxFee'] ?? 0),
            disclaimer: $data['disclaimer'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'fee_type' => $this->feeType,
            'label' => $this->label,
            'percent' => $this->percent,
            'fixed' => $this->fixed,
            'currency' => $this->currency,
            'min_fee' => $this->minFee,
            'max_fee' => $this->maxFee,
            'disclaimer' => $this->disclaimer,
        ];
    }
}
