<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCampaignLaunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'array'],
            'type.id' => ['nullable', 'integer', 'exists:campaign_types,id'],
            'type.name' => ['required', 'string', 'max:255'],
            'type.icon' => ['nullable', 'string', 'max:10'],

            'tier' => ['required', 'array'],
            'tier.id' => ['nullable', 'integer', 'exists:territory_tiers,id'],
            'tier.name' => ['required', 'string', 'max:255'],
            'tier.label' => ['nullable', 'string', 'max:255'],

            'countries' => ['array'],
            'countries.*' => ['string', 'max:100'],
            'diaspora_markets' => ['array'],
            'diaspora_markets.*' => ['string', 'max:100'],

            'channels' => ['required', 'array', 'min:1'],
            'channels.*.id' => ['nullable', 'integer', 'exists:delivery_channels,id'],
            'channels.*.name' => ['required', 'string', 'max:255'],

            'industry' => ['required', 'array'],
            'industry.id' => ['nullable', 'integer', 'exists:ad_industries,id'],
            'industry.name' => ['required', 'string', 'max:255'],
            'industry.multiplier' => ['required', 'numeric', 'min:0'],

            'frequency' => ['required', 'array'],
            'frequency.value' => ['required', 'numeric', 'min:0'],
            'frequency.label' => ['required', 'string', 'max:255'],

            'schedule' => ['required', 'array'],
            'schedule.start_date' => ['required', 'date'],
            'schedule.end_date' => ['required', 'date', 'after_or_equal:schedule.start_date'],

            'surge' => ['required', 'array'],
            'surge.active' => ['required', 'boolean'],
            'surge.option' => ['nullable', 'array'],

            'exclusivity' => ['nullable', 'array'],
            'exclusivity.id' => ['nullable', 'integer', 'exists:exclusivity_upgrades,id'],

            'pricing' => ['required', 'array'],
            'pricing.calculated_total' => ['required', 'numeric', 'min:0'],
            'pricing.final_total' => ['required', 'numeric', 'min:0'],

            'override' => ['nullable', 'array'],
            'override.amount' => ['nullable', 'numeric', 'min:0'],
            'override.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $countries = $this->input('countries', []);
            $diaspora = $this->input('diaspora_markets', []);

            if (count($countries) + count($diaspora) === 0) {
                $validator->errors()->add('markets', 'Select at least one country or diaspora market.');
            }
        });
    }
}
