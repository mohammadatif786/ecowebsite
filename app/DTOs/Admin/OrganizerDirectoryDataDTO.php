<?php

namespace App\DTOs\Admin;

class OrganizerDirectoryDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $organizers,
        public array $stats,
        public string $appURL
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'organizers' => $this->organizers,
            'stats' => $this->stats,
            'appURL' => $this->appURL,
        ];
    }
}
