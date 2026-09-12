<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DrinkPackageFormRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'bottles' => 'nullable|array',
            'bottles.*.id' => 'nullable|string',
            'bottles.*.name' => 'nullable|string',
            'bottles.*.qty' => 'nullable|integer|min:1',
            'chasers' => 'nullable|array',
            'chasers.*.id' => 'nullable|string',
            'chasers.*.name' => 'nullable|string',
            'chasers.*.qty' => 'nullable|integer|min:1',
            'waters' => 'nullable|array',
            'waters.*.id' => 'nullable|string',
            'waters.*.name' => 'nullable|string',
            'waters.*.qty' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }
}
