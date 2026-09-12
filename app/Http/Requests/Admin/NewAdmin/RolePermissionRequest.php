<?php

namespace App\Http\Requests\Admin\NewAdmin;

use App\Http\Controllers\Admin\NewAdmin\RolePermissionController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->getKey()),
            ],
            'risk' => 'required|in:Low,Medium,High,Critical',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'required|array',
            'permissions.*.module' => ['required', 'string', Rule::in(RolePermissionController::MODULES)],
            'permissions.*.view' => 'boolean',
            'permissions.*.create' => 'boolean',
            'permissions.*.edit' => 'boolean',
            'permissions.*.approve' => 'boolean',
            'permissions.*.export' => 'boolean',
            'permissions.*.delete' => 'boolean',
        ];
    }
}
