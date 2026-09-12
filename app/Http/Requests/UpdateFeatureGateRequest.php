<?php

namespace App\Http\Requests;

use App\Enums\GateLockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeatureGateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'icon'        => ['sometimes', 'string', 'max:10'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'lock_type'   => ['sometimes', Rule::enum(GateLockType::class)],
            'is_active'   => ['boolean'],
            'plan_ids'    => ['array'],
            'plan_ids.*'  => ['integer', 'exists:subscription_plans,id'],
        ];
    }
}
