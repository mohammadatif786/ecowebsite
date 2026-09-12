<?php

namespace App\DTOs\Admin;

class EventCouponsDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $coupons,
        public array $events,
        public array $stats
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'coupons' => $this->coupons,
            'events' => $this->events,
            'stats' => $this->stats,
        ];
    }
}
