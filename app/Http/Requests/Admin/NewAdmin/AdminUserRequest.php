<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminUser = $this->route('adminUser');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($adminUser?->getKey())],
            'role' => [
                'required',
                'string',
                'max:255',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            'scope' => 'nullable|string|max:255',
            'two_factor_enabled' => 'boolean',
            'status' => 'required|in:Active,Suspended',
        ];
    }
}
