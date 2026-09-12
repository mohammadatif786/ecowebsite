<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class TicketCardRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'cart' => 'required|array',
            'cart.items' => 'required|array|min:1',
            'cart.items.*.ticketId' => 'required|integer|exists:tickets,id',
            'cart.items.*.price' => 'required|numeric|min:0',
            'cart.items.*.qty' => 'required|integer|min:1',
            'cart.items.*.fee' => 'required|numeric|min:0',
            'cart.items.*.tax' => 'required|numeric|min:0',

            'cart.appliedCoupons' => 'nullable|array',
            'cart.appliedCoupons.*.couponId' => 'required|integer|exists:coupons,id',
            'cart.appliedCoupons.*.code' => 'required|string',
            'cart.appliedCoupons.*.discount' => 'required|string',
            'cart.appliedCoupons.*.discountType' => 'required|string|in:amount,percentage',
        ];
    }
}
