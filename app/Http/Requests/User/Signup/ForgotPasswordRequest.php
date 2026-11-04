<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "Forgot password?": the account email to send a reset link to.
 */
class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Enter the email you signed up with.',
            'email.email' => 'Enter a valid email address.',
        ];
    }
}
