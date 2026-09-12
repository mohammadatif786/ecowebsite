<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

abstract class SubscriptionPlanRequest extends FormRequest
{
    protected function baseRules(): array
    {
        return [
            'plan_name' => ['required', 'string', 'max:100'],
            'plan_emoji' => ['nullable', 'string', 'max:8'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'stripe_product_id' => ['required', 'string', 'starts_with:prod_'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'billing_cycle' => ['required', new Enum(BillingCycle::class)],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'status' => ['required', new Enum(SubscriptionPlanStatus::class)],
            'description' => ['nullable', 'string', 'max:2000'],

            'features' => ['nullable', 'array'],
            'features.go_live' => ['boolean'],
            'features.clubs_restaurants' => ['boolean'],
            'features.marketplace' => ['boolean'],
            'features.boost_store' => ['boolean'],
            'features.boost_events' => ['boolean'],
            'features.video_voice_calls' => ['boolean'],
            'features.the_lab' => ['boolean'],

            'perks' => ['nullable', 'array'],
            'perks.unlimited_swipes' => ['boolean'],
            'perks.see_who_liked_you' => ['boolean'],
            'perks.priority_matching_boost' => ['boolean'],
            'perks.advanced_filters' => ['boolean'],
            'perks.read_receipts' => ['boolean'],
            'perks.weekly_profile_boost' => ['boolean'],
            'perks.exclusive_plan_badge' => ['boolean'],
            'perks.priority_support' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'stripe_product_id.starts_with' => 'The Stripe Product ID must start with "prod_".',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'plan_name' => trim((string) $this->plan_name),
            'stripe_product_id' => trim((string) $this->stripe_product_id),
        ]);
    }
}
