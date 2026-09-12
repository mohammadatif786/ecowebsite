<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchantRquest extends FormRequest
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
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'st_name' => 'required|string|max:150',
            'st_owner' => 'required|string|max:150',
            'st_pickups' => 'nullable|string',
            'merchant_type' => 'required|string|max:50',
            'offers_pickup' => 'sometimes|boolean',
            'offers_delivery' => 'sometimes|boolean',
            'business_license' => 'nullable|string|max:255',
            'vat_certificate' => 'nullable|string|max:255',
            'business_license_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'vat_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country.required' => 'Country is required',
            'state.required' => 'State is required',
            'city.required' => 'City is required',
            'st_name.required' => 'Name is required',
            'st_owner.required' => 'Owner is required',
            'merchant_type.required' => 'Merchant type is required',
        ];
    }
}
