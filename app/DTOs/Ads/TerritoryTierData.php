<?php

namespace App\DTOs\Ads;

class TerritoryTierData
{
    public function __construct(
        public string $label,
        public string $name,
        public float $priceMin,
        public float $priceMax,
        public float $multiplier,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: (string) $data['label'],
            name: (string) $data['name'],
            priceMin: (float) $data['priceMin'],
            priceMax: (float) $data['priceMax'],
            multiplier: (float) $data['multiplier'],
        );
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'name' => $this->name,
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'multiplier' => $this->multiplier,
        ];
    }
}
