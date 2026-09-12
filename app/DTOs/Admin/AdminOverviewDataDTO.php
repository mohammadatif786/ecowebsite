<?php

namespace App\DTOs\Admin;

class AdminOverviewDataDTO
{
    /**
     * @param BusinessUnitDTO[] $units
     * @param CountryMetricDTO[] $countries
     */
    public function __construct(
        public array $units,
        public array $countries
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn(BusinessUnitDTO $unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn(CountryMetricDTO $country) => $country->toArray(), $this->countries),
        ];
    }
}
