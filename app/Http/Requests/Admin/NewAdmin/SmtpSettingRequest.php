<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;

class SmtpSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'host' => 'required|string|max:255',
            'port' => 'required|integer|between:1,65535',
            'encryption' => 'nullable|in:TLS,SSL,None',
            'from_name' => 'nullable|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ];
    }
}
