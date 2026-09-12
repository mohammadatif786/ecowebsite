<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => 'required_unless:shipping_method,pickup|nullable|string|max:255',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',

            'shipping_method' => 'nullable|in:standard,pickup',
            'payment_method'  => 'nullable|in:wallet,card',

            'items' => 'nullable|array|min:1',
            'items.*.id'  => 'nullable|exists:products,id',
            'items.*.qty' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'Shipping address is required.',
        ];
    }
}
