<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdCampaignLaunch extends Model
{
    protected $fillable = [
        'reference',
        'campaign_type_id',
        'territory_tier_id',
        'ad_industry_id',
        'exclusivity_upgrade_id',
        'campaign_type_snapshot',
        'territory_tier_snapshot',
        'selected_countries',
        'selected_diaspora_markets',
        'delivery_channels_snapshot',
        'industry_snapshot',
        'frequency_snapshot',
        'start_date',
        'end_date',
        'surge_active',
        'surge_snapshot',
        'exclusivity_snapshot',
        'pricing_snapshot',
        'calculated_total',
        'final_total',
        'internal_notes',
        'status',
        'launched_at',
    ];

    protected $casts = [
        'campaign_type_snapshot' => 'array',
        'territory_tier_snapshot' => 'array',
        'selected_countries' => 'array',
        'selected_diaspora_markets' => 'array',
        'delivery_channels_snapshot' => 'array',
        'industry_snapshot' => 'array',
        'frequency_snapshot' => 'array',
        'surge_active' => 'boolean',
        'surge_snapshot' => 'array',
        'exclusivity_snapshot' => 'array',
        'pricing_snapshot' => 'array',
        'calculated_total' => 'float',
        'final_total' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'launched_at' => 'datetime',
    ];
}
