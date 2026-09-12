<?php

namespace App\Http\Requests\LiveStreams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLiveStreamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('products'))) {
            $products = json_decode($this->input('products'), true);

            $this->merge(['products' => is_array($products) ? $products : []]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['public', 'followers', 'private'])],
            'subscription_rate' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'base_resolution' => ['nullable', Rule::in(['1920x1080', '1280x720'])],
            'output_resolution' => ['nullable', Rule::in(['1920x1080', '1280x720', '854x480'])],
            'products' => ['nullable', 'array', 'max:20'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=320,min_height=180,max_width=4096,max_height=4096'],
        ];
    }
}
