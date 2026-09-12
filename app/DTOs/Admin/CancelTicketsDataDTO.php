<?php

namespace App\DTOs\Admin;

class CancelTicketsDataDTO
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $requests,
        public array $orders,
        public array $users
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn($country) => $country->toArray(), $this->countries),
            'requests' => $this->requests,
            'orders' => $this->orders,
            'users' => $this->users,
        ];
    }
}
