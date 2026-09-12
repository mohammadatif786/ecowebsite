<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\EventFeeSettingRequest;
use App\Models\EventFeeSetting;

class EventFeeSettingController extends Controller
{
    public function update(EventFeeSettingRequest $request)
    {
        $data = $request->validated();

        $mapped = [
            'service_fee_pct' => $data['serviceFeePct'] ?? 0,
            'service_fee_fixed' => $data['serviceFeeFixed'] ?? 0,
            'processing_fee_pct' => $data['processingFeePct'] ?? 0,
            'processing_fee_fixed' => $data['processingFeeFixed'] ?? 0,
            'processing_linkup_share_pct' => $data['processingLinkUpSharePct'] ?? 60,
            'processing_bank_share_pct' => $data['processingBankSharePct'] ?? 40,
            'tax_rate' => $data['taxRate'] ?? 0,
            'tax_inclusive' => $data['taxInclusive'] ?? false,
            'currency' => $data['currency'] ?? 'USD',
            'drink_fee_pct' => $data['drinkFeePct'] ?? 0,
            'bottle_fee_pct' => $data['bottleFeePct'] ?? 0,
            'vip_fee_pct' => $data['vipFeePct'] ?? 0,
            'addon_fee_pct' => $data['addonFeePct'] ?? 0,
            'spa_platform_fee_pct' => $data['spaPlatformFeePct'] ?? 0,
            'spa_platform_fee_fixed' => $data['spaPlatformFeeFixed'] ?? 0,
            'spa_gratuity_enabled' => $data['spaGratuityEnabled'] ?? false,
            'spa_gratuity_default_pct' => $data['spaGratuityDefaultPct'] ?? 0,
            'spa_use_global_tax' => $data['spaUseGlobalTax'] ?? false,
            'wire_processing_fee_pct' => $data['wireProcessingFeePct'] ?? 0,
            'wire_processing_fee_fixed' => $data['wireProcessingFeeFixed'] ?? 0,
            'cookout_platform_fee_percent' => $data['cookoutPlatformFeePercent'] ?? 0,
            'cookout_platform_fee_fixed' => $data['cookoutPlatformFeeFixed'] ?? 0,
            'cookout_default_gratuity' => $data['cookoutDefaultGratuity'] ?? 0,
            'cookout_enable_gratuity' => $data['cookoutEnableGratuity'] ?? false,
        ];

        EventFeeSetting::updateOrCreate(['id' => 1], $mapped);

        return redirect()->back()->with('message', 'Event fee settings updated successfully.');
    }
}
