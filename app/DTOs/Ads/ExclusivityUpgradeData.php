<?php

namespace App\DTOs\Ads;

class ExclusivityUpgradeData
{
    public function __construct(
        public string $icon,
        public string $name,
        public int $pct,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            icon: (string) $data['icon'],
            name: (string) $data['name'],
            pct: (int) $data['pct'],
        );
    }

    public function toArray(): array
    {
        return [
            'icon' => $this->icon,
            'name' => $this->name,
            'pct' => $this->pct,
        ];
    }
}
