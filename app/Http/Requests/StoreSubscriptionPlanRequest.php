<?php

namespace App\Http\Requests;

use App\Models\SubscriptionPlan;
use Illuminate\Validation\Rule;

class StoreSubscriptionPlanRequest extends SubscriptionPlanRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('subscription create', SubscriptionPlan::class) ?? false;
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'stripe_product_id' => [
                ...$this->baseRules()['stripe_product_id'],
                Rule::unique(SubscriptionPlan::class, 'stripe_product_id')
                    ->ignore($this->route('planId')),
            ],
        ]);
    }
}
