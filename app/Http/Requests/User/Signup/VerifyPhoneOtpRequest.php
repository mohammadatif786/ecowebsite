<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route is behind `auth` and the email gate
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['otp' => preg_replace('/\D/', '', (string) $this->input('otp'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'otp' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'Enter the 6-digit code we texted you.',
            'otp.digits' => 'The code is 6 digits.',
        ];
    }
}
