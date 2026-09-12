<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'product_category_id' => 'required|numeric|exists:product_categories,id',
            'user_id' => 'required|numeric|exists:users,id',
            'description' => 'required|max:5000',
            'qty' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'listing_type' => 'nullable|string',
            'cover_image_file' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'product_category_id.required' => 'Please select a category',
            'user_id.required' => 'Please select a seller',
            'description.required' => 'Description is required',
            'qty.required' => 'Quantity is required',
            'price.required' => 'Price is required',
            'status.required' => 'Status is required',
            'cover_image_file.required' => 'Cover image is required',
        ];
    }
}
