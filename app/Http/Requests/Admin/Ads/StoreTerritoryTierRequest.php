<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerritoryTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'priceMin' => 'required|numeric|min:0',
            'priceMax' => 'required|numeric|min:0',
            'multiplier' => 'required|numeric|min:0',
        ];
    }
}
