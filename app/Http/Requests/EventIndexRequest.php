<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventIndexRequest extends FormRequest
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
            // NOTE: 'upcomming' kept (typo) to match existing frontend contract.
            // Rename to 'upcoming' on both ends if you want to fix it properly.
            'status' => ['nullable', 'string', Rule::in(['all', 'upcomming', 'live', 'completed'])],
            'search' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Invalid status filter supplied.',
        ];
    }
}
