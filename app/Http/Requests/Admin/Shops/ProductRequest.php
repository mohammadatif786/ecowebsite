<?php

namespace App\Http\Requests\Admin\Shops;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|max:255',
            'product_category_id' => 'required|numeric',
            'description' => 'required|max:5000',
            'qty' => 'required|numeric',
            'price' => 'required|numeric',
            'status' => 'required',
            'cover_image_file' => ['nullable', 'required_without:cover_image', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
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
            'name.required' => 'Name is required field',
            'name.max:255' => 'Max 255 Characters are allowed',
            'product_category_id.required' => 'Choose Product Category',
            'description.required' => 'Description is required field',
            'description.max:500' => 'Max 500 Characters are allowed',
            'qty.required' => 'Qty is required field',
            'price.required' => 'Price is required field',
            'status.required' => 'Choose Current Status',
            'cover_image_file.required_without' => 'Cover Image is required field'
        ];
    }
}
