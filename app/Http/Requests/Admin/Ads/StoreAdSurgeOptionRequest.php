<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdSurgeOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'multiplier' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
