<?php

namespace App\DTOs\Admin;

class EventCategoriesDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $categories,
        public array $stats
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'categories' => $this->categories,
            'stats' => $this->stats,
        ];
    }
}
