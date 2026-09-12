<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plan_price_id' => ['required', 'string'],
            'plan_id'       => ['required', 'integer', 'exists:subscription_plans,id'],
        ];
    }
}
