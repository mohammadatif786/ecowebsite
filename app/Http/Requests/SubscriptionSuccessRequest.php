<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionSuccessRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string'],
            'user_id'    => ['required', 'integer', 'exists:users,id'],
            'plan_id'    => ['required', 'integer', 'exists:subscription_plans,id'],
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        logger()->error('SubscriptionSuccessRequest validation failed', $validator->errors()->toArray());
        throw new \Illuminate\Validation\ValidationException($validator);
    }
}
