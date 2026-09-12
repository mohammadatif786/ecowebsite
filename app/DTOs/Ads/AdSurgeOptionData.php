<?php

namespace App\DTOs\Ads;

class AdSurgeOptionData
{
    public function __construct(
        public string $name,
        public float $multiplier,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            multiplier: (float) $data['multiplier'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'multiplier' => $this->multiplier,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
