<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
            'icon' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'duration' => 'required|string|max:20',
            'priceMin' => 'required|numeric|min:0',
            'priceMax' => 'required|numeric|min:0',
            'basePrice' => 'required|numeric|min:0',
            'reach' => 'required|integer|min:0',
            'taxRate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'isEnterprise' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'icon.required' => 'The icon field is required.',
            'name.required' => 'The name field is required.',
            'duration.required' => 'The duration field is required.',
            'priceMin.required' => 'The minimum price field is required.',
            'priceMax.required' => 'The maximum price field is required.',
            'basePrice.required' => 'The base price field is required.',
            'reach.required' => 'The reach field is required.',
            'taxRate.required' => 'The tax rate field is required.',
        ];
    }
}
