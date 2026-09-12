<?php

namespace App\Http\Requests;

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
            'category_id' => 'required|numeric',
            'city' => 'required|max:20',
            'country' => 'required|max:50',
            'coupon_visibility' => 'nullable',
            'description' => 'required|max:255',
            'disclaimer' => 'nullable',
            'email' => 'required',
            'organizer_image_object',
            'organizer_name' => 'nullable',
            'phone' => 'required|max:20',
            'state' => 'required|max:100',
            'title' => 'required|max:255',
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'early_bird_price' => 'required_if:is_free,false',
            'vip_price' => 'required_if:is_free,false',
            'type' => 'required',
            'venue' => 'nullable|max:255',
            'website' => 'nullable|max:100',
            'featured_image' => ['nullable', 'string'],
            'image_file' => ['nullable', 'required_without:featured_image', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
