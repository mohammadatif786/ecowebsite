<?php

namespace Database\Seeders;

use App\Models\DeliveryChannel;
use App\Models\ExclusivityUpgrade;
use App\Models\TerritoryTier;
use App\Models\AdIndustry;
use App\Models\AdSurgeOption;
use Illuminate\Database\Seeder;

class CampaignBuilderDefaultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TerritoryTier::count() === 0) {
            TerritoryTier::insert([
                ['label' => 'Tier 1', 'name' => 'Local Territory', 'price_min' => 250, 'price_max' => 750, 'multiplier' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Tier 2', 'name' => 'National Territory', 'price_min' => 750, 'price_max' => 2500, 'multiplier' => 1.8, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Tier 3', 'name' => 'Multi-Country Caribbean', 'price_min' => 3000, 'price_max' => 10000, 'multiplier' => 3.5, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Tier 4', 'name' => 'Caribbean + Diaspora', 'price_min' => 10000, 'price_max' => 35000, 'multiplier' => 7, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Tier 5', 'name' => 'Central America + Caribbean', 'price_min' => 15000, 'price_max' => 50000, 'multiplier' => 12, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Tier 6', 'name' => 'Latin America Network', 'price_min' => 50000, 'price_max' => 250000, 'multiplier' => 25, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DeliveryChannel::count() === 0) {
            DeliveryChannel::insert([
                ['icon' => '🔔', 'name' => 'Push Notifications', 'price' => 0, 'unit' => null, 'locked' => true, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '📱', 'name' => 'In-App Feed', 'price' => 500, 'unit' => 'month', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '✉️', 'name' => 'Email Sponsorships', 'price' => 750, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🎪', 'name' => 'Event Integrations', 'price' => 1000, 'unit' => 'event', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🏠', 'name' => 'Homepage Placements', 'price' => 1200, 'unit' => 'week', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🛍️', 'name' => 'Marketplace Ads', 'price' => 400, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '💳', 'name' => 'Wallet Sponsorships', 'price' => 600, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🔴', 'name' => 'LinkUp Live', 'price' => 2000, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '💜', 'name' => 'Match Feed', 'price' => 800, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🔔', 'name' => 'Sponsored Notifications', 'price' => 350, 'unit' => 'campaign', 'locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (ExclusivityUpgrade::count() === 0) {
            ExclusivityUpgrade::insert([
                ['icon' => '🍺', 'name' => 'Exclusive Beer Sponsor', 'pct' => 300, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🥃', 'name' => 'Exclusive Rum Sponsor', 'pct' => 300, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🍸', 'name' => 'Exclusive Vodka Sponsor', 'pct' => 250, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '✈️', 'name' => 'Exclusive Airline', 'pct' => 400, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '📡', 'name' => 'Exclusive Telecom', 'pct' => 350, 'created_at' => now(), 'updated_at' => now()],
                ['icon' => '🏧', 'name' => 'Exclusive Bank', 'pct' => 250, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (AdIndustry::count() === 0) {
            AdIndustry::insert([
                ['name' => 'General', 'slug' => 'general', 'multiplier' => 1.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Food & Beverage', 'slug' => 'food', 'multiplier' => 1.5, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Alcohol Brands', 'slug' => 'alcohol', 'multiplier' => 2.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Nightlife & Entertainment', 'slug' => 'nightlife', 'multiplier' => 1.8, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Airlines & Travel', 'slug' => 'airlines', 'multiplier' => 2.5, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tourism Boards', 'slug' => 'tourism', 'multiplier' => 3.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Telecoms', 'slug' => 'telecoms', 'multiplier' => 2.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Banking & Finance', 'slug' => 'banking', 'multiplier' => 1.5, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Real Estate', 'slug' => 'realestate', 'multiplier' => 1.3, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Fashion & Lifestyle', 'slug' => 'fashion', 'multiplier' => 1.4, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Political Campaigns', 'slug' => 'political', 'multiplier' => 5.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Casinos & Gaming', 'slug' => 'casino', 'multiplier' => 3.0, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (AdSurgeOption::count() === 0) {
            AdSurgeOption::insert([
                ['name' => 'Normal Weekend', 'multiplier' => 1.5, 'description' => '1.5×', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Concert Weekend', 'multiplier' => 2.0, 'description' => '2×', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tourism Season', 'multiplier' => 2.0, 'description' => '2×', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Carnival Weekend', 'multiplier' => 4.0, 'description' => '3×–5×', 'created_at' => now(), 'updated_at' => now()],
                ['name' => "New Year's Eve", 'multiplier' => 5.0, 'description' => '5×', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Election Season', 'multiplier' => 4.0, 'description' => '4×', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
