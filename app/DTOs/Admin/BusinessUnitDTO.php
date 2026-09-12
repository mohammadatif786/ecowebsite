<?php

namespace App\DTOs\Admin;

class BusinessUnitDTO
{
    public function __construct(
        public string $key,
        public string $name,
        public string $icon,
        public float $platformRate,
        public float $bankRate,
        public float $costRate,
        public string $desc
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['name'],
            $data['icon'],
            $data['platformRate'],
            $data['bankRate'],
            $data['costRate'],
            $data['desc']
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'icon' => $this->icon,
            'platformRate' => $this->platformRate,
            'bankRate' => $this->bankRate,
            'costRate' => $this->costRate,
            'desc' => $this->desc,
        ];
    }
}
