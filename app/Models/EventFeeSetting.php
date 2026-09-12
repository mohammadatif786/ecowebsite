<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFeeSetting extends Model
{
    protected $fillable = [
        'service_fee_pct',
        'service_fee_fixed',
        'processing_fee_pct',
        'processing_fee_fixed',
        'processing_linkup_share_pct',
        'processing_bank_share_pct',
        'tax_rate',
        'tax_inclusive',
        'currency',
        'drink_fee_pct',
        'bottle_fee_pct',
        'vip_fee_pct',
        'addon_fee_pct',
        'spa_platform_fee_pct',
        'spa_platform_fee_fixed',
        'spa_gratuity_enabled',
        'spa_gratuity_default_pct',
        'spa_use_global_tax',
        'spa_tax_rate',
        'spa_tax_inclusive',
        'wire_processing_fee_pct',
        'wire_processing_fee_fixed',
        'cookout_platform_fee_percent',
        'cookout_platform_fee_fixed',
        'cookout_default_gratuity',
        'cookout_enable_gratuity',
    ];

    protected $casts = [
        'service_fee_pct' => 'decimal:2',
        'service_fee_fixed' => 'decimal:2',
        'processing_fee_pct' => 'decimal:2',
        'processing_fee_fixed' => 'decimal:2',
        'processing_linkup_share_pct' => 'decimal:2',
        'processing_bank_share_pct' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'drink_fee_pct' => 'decimal:2',
        'bottle_fee_pct' => 'decimal:2',
        'addon_fee_pct' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'spa_gratuity_enabled' => 'boolean',
        'spa_use_global_tax' => 'boolean',
        'spa_tax_inclusive' => 'boolean',
        'wire_processing_fee_fixed' => 'decimal:2',
        'cookout_platform_fee_percent' => 'decimal:2',
        'cookout_platform_fee_fixed' => 'decimal:2',
        'cookout_default_gratuity' => 'decimal:2',
        'cookout_enable_gratuity' => 'boolean',
    ];
}
