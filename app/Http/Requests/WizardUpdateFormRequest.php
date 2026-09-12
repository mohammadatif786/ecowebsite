<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WizardUpdateFormRequest extends FormRequest
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
            'avatar' => 'nullable',
            'country' => 'required|string',
            'gender' => 'required|string|in:male,female,other',
            'birthday' => 'required|date',
            'state' => 'required|string',
            'city' => 'required|string',
            'interests' => 'required',
            'link_me_with' => 'required|string',
            'age_filter' => 'required',
            'distance_filter' => 'required',
            'link_me_with_country_name' => 'required|string',
            'about_me' => 'required|string|max:1000',
            'job' => 'required|string|max:255',
            'university' => 'required|string|max:255',
            'whyare' => 'required',
            'phone_number' => 'required',
            'language' => 'required',
            'religion' => 'nullable|string|max:100',
            'caribbean_interest' => 'required',
            'more_photos'   => 'required|array',
            'more_photos.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'more_photos.*.max'   => 'Your uploaded image size is more than 5MB.',
            'more_photos.*.image' => 'Only image files are allowed.',
            'more_photos.*.mimes' => 'Only jpeg, jpg, and png formats are supported.',
            'job.max' => 'Job description must not exceed 255 characters.',
            'about_me.max' => 'About me must not exceed 1000 characters.',
            'university.max' => 'University name must not exceed 255 characters.',
        ];
    }
}
