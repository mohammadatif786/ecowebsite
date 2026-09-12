<?php

namespace App\DTOs\Ads;

class DeliveryChannelData
{
    public function __construct(
        public string $icon,
        public string $name,
        public float $price,
        public ?string $unit,
        public bool $locked,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            icon: (string) $data['icon'],
            name: (string) $data['name'],
            price: (float) $data['price'],
            unit: isset($data['unit']) ? (string) $data['unit'] : null,
            locked: (bool) ($data['locked'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'icon' => $this->icon,
            'name' => $this->name,
            'price' => $this->price,
            'unit' => $this->unit,
            'locked' => $this->locked,
        ];
    }
}
