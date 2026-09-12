<?php

namespace App\DTOs\Ads;

class CampaignTypeData
{
    public function __construct(
        public string $icon,
        public string $name,
        public string $duration,
        public float $priceMin,
        public float $priceMax,
        public float $basePrice,
        public float $reach,
        public float $taxRate,
        public ?string $description,
        public bool $isEnterprise = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            icon: (string) $data['icon'],
            name: (string) $data['name'],
            duration: (string) $data['duration'],
            priceMin: (float) $data['priceMin'],
            priceMax: (float) $data['priceMax'],
            basePrice: (float) $data['basePrice'],
            reach: (float) $data['reach'],
            taxRate: (float) $data['taxRate'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            isEnterprise: (bool) ($data['isEnterprise'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'icon' => $this->icon,
            'name' => $this->name,
            'duration' => $this->duration,
            'priceMin' => $this->priceMin,
            'priceMax' => $this->priceMax,
            'basePrice' => $this->basePrice,
            'reach' => $this->reach,
            'taxRate' => $this->taxRate,
            'description' => $this->description,
            'is_enterprise' => $this->isEnterprise,
        ];
    }
}
