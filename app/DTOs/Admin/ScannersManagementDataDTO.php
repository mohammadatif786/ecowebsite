<?php

namespace App\DTOs\Admin;

class ScannersManagementDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $scanners,
        public array $organizers,
        public array $stats,
        public string $appURL
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'scanners' => $this->scanners,
            'organizers' => $this->organizers,
            'stats' => $this->stats,
            'appURL' => $this->appURL,
        ];
    }
}
