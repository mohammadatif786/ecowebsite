<?php

namespace App\DTOs\Admin;

class UsersDashboardDataDTO
{
    /**
     * @param BusinessUnitDTO[] $units
     * @param CountryMetricDTO[] $countries
     * @param array $userProfiles
     * @param array $subscriptionPlans
     * @param array $growthStats
     */
    public function __construct(
        public array $units,
        public array $countries,
        public array $userProfiles,
        public array $subscriptionPlans,
        public array $growthStats
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn(BusinessUnitDTO $unit) => $unit->toArray(), $this->units),
            'countries' => array_map(fn(CountryMetricDTO $country) => $country->toArray(), $this->countries),
            'userProfiles' => $this->userProfiles,
            'subscriptionPlans' => $this->subscriptionPlans,
            'growthStats' => $this->growthStats,
        ];
    }
}
