<?php

namespace App\Http\Requests;

use App\Enums\GateLockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeatureGateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'icon'        => ['required', 'string', 'max:10'],
            'description' => ['required', 'string', 'max:1000'],
            'lock_type'   => ['required', Rule::enum(GateLockType::class)],
            'is_active'   => ['boolean'],
            'plan_ids'    => ['array'],
            'plan_ids.*'  => ['integer', 'exists:subscription_plans,id'],
        ];
    }
}
