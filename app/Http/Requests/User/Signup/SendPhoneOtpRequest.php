<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

class SendPhoneOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route is behind `auth` and the email gate
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
        ];
    }
}
