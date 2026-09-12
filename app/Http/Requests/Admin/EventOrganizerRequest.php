<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EventOrganizerRequest extends FormRequest
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
        return $this->isMethod('post') ? $this->storeRules() : $this->updateRules();
    }
    /**
     * Validation rules for storing a new event organizer.
     */
    private function storeRules(): array
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'status' => 'nullable|max:150',
            'password' => 'nullable|max:255',
            'email' => 'required|email',
            'type' => 'nullable',
            'phone_number' => 'nullable|max:100',
            'address' => 'nullable|max:255',
            'website' => 'nullable|url',
            'radio' => 'nullable|string|max:255',
            'passport_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driver_license_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
    /**
     * Validation rules for updating an existing event organizer.
     */
    private function updateRules(): array
    {
        return [
            'first_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'status' => 'nullable|max:150',
            'password' => 'nullable|max:255',
            'email' => 'nullable|email',
            'type' => 'nullable',
            'phone_number' => 'nullable|max:100',
            'address' => 'nullable|max:255',
            'website' => 'nullable|url',
            'radio' => 'nullable|string|max:255',
            'passport_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driver_license_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
