<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;

class EventFeeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serviceFeePct' => 'nullable|numeric|min:0',
            'serviceFeeFixed' => 'nullable|numeric|min:0',
            'processingFeePct' => 'nullable|numeric|min:0',
            'processingFeeFixed' => 'nullable|numeric|min:0',
            'processingLinkUpSharePct' => 'nullable|numeric|min:0|max:100',
            'processingBankSharePct' => 'nullable|numeric|min:0|max:100',
            'taxRate' => 'nullable|numeric|min:0',
            'taxInclusive' => 'nullable|boolean',
            'currency' => 'nullable|string|max:10',
            'drinkFeePct' => 'nullable|numeric|min:0',
            'bottleFeePct' => 'nullable|numeric|min:0',
            'vipFeePct' => 'nullable|numeric|min:0',
            'addonFeePct' => 'nullable|numeric|min:0',
            'spaPlatformFeePct' => 'nullable|numeric|min:0',
            'spaPlatformFeeFixed' => 'nullable|numeric|min:0',
            'spaGratuityEnabled' => 'nullable|boolean',
            'spaGratuityDefaultPct' => 'nullable|numeric|min:0',
            'spaUseGlobalTax' => 'nullable|boolean',
            'wireProcessingFeePct' => 'nullable|numeric|min:0',
            'wireProcessingFeeFixed' => 'nullable|numeric|min:0',
            'cookoutPlatformFeePercent' => 'nullable|numeric|min:0',
            'cookoutPlatformFeeFixed' => 'nullable|numeric|min:0',
            'cookoutDefaultGratuity' => 'nullable|numeric|min:0',
            'cookoutEnableGratuity' => 'nullable|boolean',
        ];
    }
}
