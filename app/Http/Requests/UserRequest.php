<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => 'required|email',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'about_me'   => 'required|max:500',
            'gender' => 'required|in:male,female',
            'phone_number' => 'required|max:100',
            'age' => 'nullable',
            'birthday' => 'required|date',
            'country'    => 'required|max:150',
            'state'    => 'required|max:150',
            'city'    => 'required|max:150',
            'job' => 'required',
            'university' => 'required|max:150',
            'link_me_with_country_code' => 'nullable|max:150',
            'caribbean_interest' => 'required|max:150',
            'status' => 'required|max:150',
            'avatar' => 'required',
            'link_with_me_phone_code' => 'nullable|max:150',
            'password' => 'nullable'
        ];
    }
}
