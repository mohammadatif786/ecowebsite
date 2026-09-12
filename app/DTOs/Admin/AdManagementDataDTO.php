<?php

namespace App\DTOs\Admin;

class AdManagementDataDTO
{
    public function __construct(
        public array $ads,
        public array $emailAds,
        public array $stats,
        public array $revenueByChannel,
        public array $activityFeed,
        public array $needsAttention,
        public array $emailCategories = [],
        public array $emailCountries = [],
        public array $c360NewsAds = [],
        public array $campaignTypes = [],
        public array $territoryTiers = [],
        public array $deliveryChannels = [],
        public array $exclusivityUpgrades = [],
        public array $adIndustries = [],
        public array $adSurgeOptions = [],
    ) {}

    public function toArray(): array
    {
        return [
            'ads' => $this->ads,
            'emailAds' => $this->emailAds,
            'stats' => $this->stats,
            'revenueByChannel' => $this->revenueByChannel,
            'activityFeed' => $this->activityFeed,
            'needsAttention' => $this->needsAttention,
            'emailCategories' => $this->emailCategories,
            'emailCountries' => $this->emailCountries,
            'c360NewsAds' => $this->c360NewsAds,
            'campaignTypes' => $this->campaignTypes,
            'territoryTiers' => $this->territoryTiers,
            'deliveryChannels' => $this->deliveryChannels,
            'exclusivityUpgrades' => $this->exclusivityUpgrades,
            'adIndustries' => $this->adIndustries,
            'adSurgeOptions' => $this->adSurgeOptions,
        ];
    }
}
