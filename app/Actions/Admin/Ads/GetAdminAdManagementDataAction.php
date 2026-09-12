<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Admin\AdManagementDataDTO;
use App\Models\AdIndustry;
use App\Models\AdSurgeOption;
use App\Models\CampaignType;
use App\Models\DeliveryChannel;
use App\Models\ExclusivityUpgrade;
use App\Models\TerritoryTier;
use App\Services\Admin\Ads\AdvertisementService;

class GetAdminAdManagementDataAction
{
    public function __construct(
        private AdvertisementService $service
    ) {}

    public function execute(): AdManagementDataDTO
    {
        $adData = $this->service->getAllAdvertisements();

        $channelValues = [
            ['name' => 'C360 News', 'value' => 0.0, 'color' => '#f97316'],
            ['name' => 'Swipe Ads', 'value' => (float) $adData['stats']['total_revenue'], 'color' => '#29C9E8'],
            ['name' => 'Email Ads', 'value' => (float) $adData['stats']['email']['revenue'], 'color' => '#14b8a6'],
            ['name' => 'Clubs', 'value' => 0.0, 'color' => '#8b5cf6'],
            ['name' => 'Pending', 'value' => 0.0, 'color' => '#ef4444'],
        ];
        $maxChannelValue = max(array_column($channelValues, 'value')) ?: 1;
        $revenueByChannel = array_map(fn ($channel) => [
            ...$channel,
            'width' => round($channel['value'] / $maxChannelValue * 100) . '%',
        ], $channelValues);

        $activityFeed = [
            ['type' => 'orange', 'text' => '<strong>Sandals Resorts</strong> C360 News placement reached <strong>61,400 impressions</strong>', 'time' => '2 minutes ago'],
            ['type' => 'green', 'text' => '<strong>Heineken Caribbean</strong> campaign renewed · $22,500 secured', 'time' => '18 minutes ago'],
            ['type' => 'blue', 'text' => '<strong>Digicel</strong> Newsletter Spot clicked <strong>1,604 times</strong> this week', 'time' => '1 hour ago'],
            ['type' => 'amber', 'text' => '<strong>Club Nova</strong> ad expiring — renewal recommended', 'time' => '3 hours ago'],
            ['type' => 'orange', 'text' => '<strong>Scotiabank</strong> C360 News Category Takeover — 2,890 clicks · 5.9% CTR', 'time' => 'Today, 9:14 AM'],
        ];

        $needsAttention = [
            ['type' => 'red', 'title' => 'Miami Eats — Payment Pending', 'sub' => 'Campaign ended · $49 outstanding', 'action' => 'View', 'viewId' => 'adReportsCommand'],
            ['type' => 'amber', 'title' => 'Club Nova — Expires in 4 days', 'sub' => '18,700 impressions · 5.2% CTR', 'action' => 'Renew', 'viewId' => 'adReportsCommand'],
            ['type' => 'blue', 'title' => 'Neon Lounge — Starts in 14 days', 'sub' => '$349 Premium · South Beach', 'action' => 'Preview', 'viewId' => 'adReportsCommand'],
        ];

        return new AdManagementDataDTO(
            $adData['ads']->toArray(),
            $adData['emailAds']->toArray(),
            $adData['stats'],
            $revenueByChannel,
            $activityFeed,
            $needsAttention,
            $this->service->getEmailAdCategoriesWithCounts()->toArray(),
            $this->service->getCountries()->toArray(),
            $this->service->getC360NewsAds()->toArray(),
            CampaignType::all()->toArray(),
            TerritoryTier::all()->toArray(),
            DeliveryChannel::all()->toArray(),
            ExclusivityUpgrade::all()->toArray(),
            AdIndustry::where('is_active', true)->get()->toArray(),
            AdSurgeOption::where('is_active', true)->get()->toArray()
        );
    }
}
