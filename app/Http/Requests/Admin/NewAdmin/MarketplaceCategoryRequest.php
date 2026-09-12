<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:product_categories,name,' . ($this->category ? $this->category->id : 'NULL'),
            'status' => 'required|boolean',
            'is_featured' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'This category name already exists',
            'status.required' => 'Status is required',
        ];
    }
}
