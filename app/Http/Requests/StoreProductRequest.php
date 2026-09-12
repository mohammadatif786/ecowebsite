<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
        $listingType = $this->input('listing_type');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'listing_type' => ['required', 'string'],
            'seller_owner' => [
                Rule::requiredIf(in_array($listingType, ['Store', 'Carnival Group'])),
                'nullable',
                'integer',
                Rule::when(in_array($listingType, ['Store', 'Carnival Group']), 'exists:merchants,id'),
            ],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif',
                'max:5120',
            ],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
            'commMode' => ['nullable', 'string', Rule::in(['pct', 'flat', 'none'])],
            'commission' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'commFlat' => ['nullable', 'numeric', 'min:0'],
            'collect_tax' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $listingType = $this->input('listing_type');

        if ($listingType === 'Individual') {
            $this->merge([
                'seller_owner' => null
            ]);
        } elseif ($listingType === 'Administrative') {
            $this->merge([
                'seller_owner' => null
            ]);
        }
    }

    /**
     *
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'price.required' => 'Price is required',
            'category_id.required' => 'Category is required',
            'listing_type.required' => 'List Type is required',
            'seller_owner.required' => 'Seller is required for Store or Group',
            'cover_image.image' => 'Cover must be an image file',
            'cover_image.mimes' => 'Cover must be jpeg, png, or gif',
            'cover_image.max' => 'Cover image must be 5MB or smaller',
            'images.max' => 'You can upload up to 10 product images.',
            'images.*.image' => 'Each product image must be an image file.',
            'images.*.mimes' => 'Product images must be JPEG, PNG, or GIF files.',
            'images.*.max' => 'Each product image must be 5MB or smaller.',
            'commMode.in' => 'Choose percentage, flat rate, or exclude for promoter commission',
            'commission.max' => 'Percentage commission cannot be more than 50%',
        ];
    }
}
