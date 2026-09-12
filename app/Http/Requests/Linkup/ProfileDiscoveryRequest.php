<?php

namespace App\Http\Requests\Linkup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileDiscoveryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'age_min' => ['nullable', 'integer', 'min:15', 'max:120'], 'age_max' => ['nullable', 'integer', 'min:15', 'max:120', 'gte:age_min'],
            'distance_min' => ['nullable', 'numeric', 'min:0'], 'distance_max' => ['nullable', 'numeric', 'min:0', 'gte:distance_min'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'both'])],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'interests' => ['nullable', 'array', 'max:20'], 'interests.*' => ['string', 'max:100'],
            'languages' => ['nullable', 'array', 'max:10'], 'languages.*' => ['string', 'max:100'],
            'religions' => ['nullable', 'array', 'max:10'], 'religions.*' => ['string', 'max:100'],
            'goals' => ['nullable', 'array', 'max:10'], 'goals.*' => ['string', 'max:100'],
        ];
    }
}
