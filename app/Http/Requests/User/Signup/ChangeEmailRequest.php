<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route is behind `auth`
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Uniqueness is checked by ChangeUnverifiedEmailAction, which ignores the member's own row.
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }
}
