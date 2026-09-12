<?php

namespace App\DTOs\Admin;

class EventSponsorsDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $sponsors,
        public array $events,
        public array $stats,
        public string $appURL
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'sponsors' => $this->sponsors,
            'events' => $this->events,
            'stats' => $this->stats,
            'appURL' => $this->appURL,
        ];
    }
}
