<?php

namespace App\DTOs;

class FeatureGateData
{
    public function __construct(
        public readonly string $name,
        public readonly string $icon,
        public readonly string $description,
        public readonly string $lockType,
        public readonly bool $isActive,
        public readonly array $planIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            icon: $data['icon'],
            description: $data['description'],
            lockType: $data['lock_type'],
            isActive: $data['is_active'] ?? true,
            planIds: $data['plan_ids'] ?? [],
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'name'      => $this->name,
            'icon'      => $this->icon,
            'description' => $this->description,
            'lock_type' => $this->lockType,
            'is_active' => $this->isActive,
        ];
    }
}
