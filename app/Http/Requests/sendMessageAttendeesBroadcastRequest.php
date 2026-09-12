<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class sendMessageAttendeesBroadcastRequest extends FormRequest
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
            "event_id" => "required|exists:link_up_events,id",
            "audience" => "nullable",
            "preset" => "nullable",
            "subject" => "required|string|max:255",
            "body" => "required|string|max:1000",
            "chLinkUp" => "required|boolean",
            "chEmail" => "required|boolean",
            "chSMS" => "required|boolean",
            "confirmText" => "required|string|in:SEND",
            "totalSent" => "nullable|integer",
            "totalDelivered" => "nullable|integer",
            "totalFailed" => "nullable|integer",
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'Event ID is required.',
            'event_id.exists' => 'The selected event does not exist.',
            'subject.required' => 'Subject is required.',
            'subject.max' => 'Subject cannot exceed 255 characters.',
            'body.required' => 'Message body is required.',
            'body.max' => 'Message body cannot exceed 1000 characters.',
            'chLinkUp.required' => 'LinkUp channel selection is required.',
            'chLinkUp.boolean' => 'LinkUp channel must be true or false.',
            'chEmail.required' => 'Email channel selection is required.',
            'chEmail.boolean' => 'Email channel must be true or false.',
            'chSMS.required' => 'SMS channel selection is required.',
            'chSMS.boolean' => 'SMS channel must be true or false.',
            'confirmText.required' => 'Type SEND to confirm .',
            'confirmText.in' => 'Invalid confirmation text. Please type SEND to confirm.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'Event ID',
            'subject' => 'Subject',
            'body' => 'Message Body',
            'chLinkUp' => 'LinkUp Channel',
            'chEmail' => 'Email Channel',
            'chSMS' => 'SMS Channel',
            'confirmText' => 'Confirmation Text',
            'totalSent' => 'Total Sent',
            'totalDelivered' => 'Total Delivered',
            'totalFailed' => 'Total Failed',
        ];
    }
}
