<?php

namespace App\DTOs\Ads;

use Illuminate\Support\Str;

class AdIndustryData
{
    public function __construct(
        public string $name,
        public float $multiplier,
        public ?string $slug = null,
        public bool $isActive = true,
    ) {
        $this->slug = $slug ?? Str::slug($name);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            multiplier: (float) $data['multiplier'],
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'multiplier' => $this->multiplier,
            'is_active' => $this->isActive,
        ];
    }
}
