<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsueStoreRequest extends FormRequest
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
            'frequency' => 'required|string|in:weekly,monthly',
            'hand_amount' => 'required|numeric|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'max_members' => 'required|integer|min:1',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'disclaimerAgreed' => 'required|accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name.',
            'frequency.required' => 'Please select a frequency.',
            'hand_amount.required' => 'Please enter the hand amount.',
            'start_date.required' => 'Please select a start date.',
            'max_members.required' => 'Please enter the maximum number of members.',
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.*.exists' => 'The selected user is not valid.',
            'disclaimerAgreed.accepted' => 'You must agree to the disclaimer.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'frequency' => 'Frequency',
            'hand_amount' => 'Hand Amount',
            'start_date' => 'Start Date',
            'max_members' => 'Max Members',
            'user_ids' => 'Users',
            'disclaimerAgreed' => 'Disclaimer',
        ];
    }
}
