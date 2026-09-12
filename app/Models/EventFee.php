<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFee extends Model
{
    protected $fillable = [
        'enable_subscriptions',
        'platform_share_base_pct',
        'creator_share_base_pct',
        'use_tiers',

        'enable_private_subs',
        'private_split_mode',
        'min_private_sub_price',
        'max_private_sub_price',

        'live_shop_fee_pct',
        'live_shop_processing_fee_pct',
        'live_shop_processing_fee_fixed',

        'wire_processing_fee_pct',
        'payout_threshold',
        'payout_hold_days',

        'tax_rate',
        'tax_inclusive',
        'tiers',
        'currency',
    ];

    protected $casts = [
        'enable_subscriptions' => 'boolean',
        'use_tiers' => 'boolean',
        'enable_private_subs' => 'boolean',
        'tax_inclusive' => 'boolean',
        'platform_share_base_pct' => 'float',
        'creator_share_base_pct' => 'float',
        'live_shop_fee_pct' => 'float',
        'live_shop_processing_fee_pct' => 'float',
        'live_shop_processing_fee_fixed' => 'float',
        'wire_processing_fee_pct' => 'float',
        'payout_threshold' => 'float',
        'payout_hold_days' => 'integer',
        'tax_rate' => 'float',
    ];
}
