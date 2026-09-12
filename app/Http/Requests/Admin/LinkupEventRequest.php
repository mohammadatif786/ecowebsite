<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LinkUpEventRequest extends FormRequest
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
            'title' => 'required|max:255',
            'description' => 'required|max:1500',
            'category_id' => 'required|numeric',
            'disclaimer' => 'nullable|max:1500',
            'country' => 'required|max:50',
            'state' => 'required|max:100',
            'city' => 'required|max:20',
            'email' => 'required',
            'organizer_name' => 'nullable',
            'phone' => 'required|max:20',
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'type' => 'required',
            'venue' => 'required|max:255',
            'website' => 'nullable|max:100',
            'cover_image_file' => ['nullable'],
            'organizer_image_file' => ['nullable'],
            'image_file' => 'nullable',
            'latitude' => 'nullable|numeric',
            'longtitude' => 'nullable|numeric',
            'status' => 'nullable|string|max:50', // Added status field validation
        ];
    }
}
