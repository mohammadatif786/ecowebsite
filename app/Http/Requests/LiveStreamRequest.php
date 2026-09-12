<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LiveStreamRequest extends FormRequest
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
            'title'    =>  'required|string',
            'start_time' => 'required|date',
            'broadcast_type' => 'required',
            'visibility' => 'required',
            'thumbnail' => 'nullable|image',
            'resolution' => 'required|string|in:240p,360p,480p,540p,720p,1080p,1440p,2160p',
        ];
    }
}
