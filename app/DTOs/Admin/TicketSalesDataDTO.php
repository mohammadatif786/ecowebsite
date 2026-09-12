<?php

namespace App\DTOs\Admin;

class TicketSalesDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $records,
        public array $stats
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'records' => $this->records,
            'stats' => $this->stats,
        ];
    }
}
