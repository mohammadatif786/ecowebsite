<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EventSponsorRequest extends FormRequest
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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch') || $this->has('_method');
        
        return [
            'name' => 'required|max:255',
            'description' => 'required|max:1000',
            'status' => 'required',
            'link_up_event_id' => 'required|numeric',
            'sponsor_image_file' => $isUpdate 
                ? ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg', 'max:20480']
                : ['nullable', 'required_without:image_object', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg', 'max:20480'],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name Is Required',
            'description.required' => 'Description Is Required',
            'status.required' => 'Select Status',
            'link_up_event_id.required' => 'Choose Linkup Event',
            'sponsor_image_file.required_without' => 'Image Upload Is Mandatory',
        ];
    }
}
