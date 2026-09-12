<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
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
            'body' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'template_id' => ['nullable', 'numeric'],
            'send_to_bulk' => ['required', 'boolean'],
            'send_to_by' => ['nullable', 'required_if:send_to_bulk,true'],
            // 'send_to_by_value' => ['nullable', 'required_if:send_to_bulk,true', 'string'],
            'send_to_by_value' => [
                'nullable',
                'requiredIf' => function () {
                    return $this->input('send_to_bulk') === true && $this->input('send_to_by') !== 'all';
                },
                'string'
            ],
            'send_to_email' => ['nullable', 'required_if:send_to_bulk,false', 'email'],
        ];
    }


    public function messages()
    {
        return [
            'body.required' => 'Email body is required',
            'subject.required' => 'Email subject is required',
            'send_to_bulk.required' => 'Send To bulk is required',
            'send_to_bulk.boolean' => 'Send To bulk must be true or false',
            'send_to_email.required' => 'An email must be provided when send to buld is false',
            'send_to_by.required_if' => 'Option is required when send to bulk is true',
            'send_to_by_value.required_if' => 'Field is required when send to bulk is true',

        ];
    }
}
