<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserRequest extends FormRequest
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
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'birthday' => 'nullable|date',
            'gender' => 'required|in:Male,Female',
            'about_me'   => 'nullable|max:500',
            'country'    => 'nullable|max:150',
            'state'    => 'nullable|max:150',
            'city'    => 'nullable|max:150',
            'link_with_me_phone_code' => 'nullable|max:150',
            'phone_number' => 'nullable|max:100',
            'status'     => 'required',
            'type' => 'required',
            // Password fields
            'password'   => $this->isMethod('post') ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
            'password_confirmation' => $this->isMethod('post') ? 'required|string|min:8' : 'nullable|string|min:8',
        ];
    }

    public function messages(): array
    {
        return ['type.required' => 'The Role is required'];
    }
}
