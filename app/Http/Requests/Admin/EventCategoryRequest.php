<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EventCategoryRequest extends FormRequest
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
            'name'=>'required|max:255',
            'is_featured'=>'required',
            'status'=>'required',
            'category_image_file' => $isUpdate 
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']
                : ['nullable', 'required_without:image_object', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
