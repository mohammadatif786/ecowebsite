<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class StoreExclusivityUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'pct' => 'required|integer|min:1|max:2000',
        ];
    }
}
