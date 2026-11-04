<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route is behind `auth`
    }

    protected function prepareForValidation(): void
    {
        // Pasted codes often carry a space or dash ("123 456").
        $this->merge(['code' => preg_replace('/\D/', '', (string) $this->input('code'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:'.config('signup.email_code.length')],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Enter the 6-digit code we emailed you.',
            'code.digits' => 'The code is 6 digits.',
        ];
    }
}
