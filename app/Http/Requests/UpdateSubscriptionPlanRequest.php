<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSubscriptionPlanRequest extends SubscriptionPlanRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('subscription update', $this->route('subscription_plan')) ?? false;
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'stripe_product_id' => [
                ...$this->baseRules()['stripe_product_id'],
                Rule::unique('subscription_plans', 'stripe_product_id')
                    ->ignore($this->route('subscription_plan')),
            ],
        ]);
    }
}
