<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'enableSubscriptions' => ['nullable', 'boolean'],
            'platformShareBasePct' => ['nullable', 'numeric'],
            'creatorShareBasePct' => ['nullable', 'numeric'],
            'useTiers' => ['nullable', 'boolean'],
            'enablePrivateSubs' => ['nullable', 'boolean'],
            'privateSplitMode' => ['nullable', 'in:fixed50,tiers'],
            'minPrivateSubPrice' => ['nullable', 'numeric'],
            'maxPrivateSubPrice' => ['nullable', 'numeric'],
            'liveShopFeePct' => ['nullable', 'numeric'],
            'liveShopProcessingFeePct' => ['nullable', 'numeric'],
            'liveShopProcessingFeeFixed' => ['nullable', 'numeric'],
            'wireProcessingFeePct' => ['nullable', 'numeric'],
            'payoutThreshold' => ['nullable', 'numeric'],
            'payoutHoldDays' => ['nullable', 'integer'],
            'taxRate' => ['nullable', 'numeric'],
            'taxInclusive' => ['nullable', 'boolean'],
            'currency' => ['nullable', 'string'],
            'tiers' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'platformShareBasePct.min' => 'Platform share must be at least 50%.',
            'tiers.*.platformPct.min' => 'Each tier must allocate at least 50% to platform.',
            'currency.size' => 'Currency code must be exactly 3 characters (ISO 4217).',
        ];
    }
}
