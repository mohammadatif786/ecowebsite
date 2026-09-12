<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
            'link_up_event_id' => 'required|numeric',
            'code' => 'required|max:30',
            'title' => 'required',
            'description' => 'required',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
            'discount_type' => 'required|in:amount,percentage',
            'status' => 'required',
            'image_file' => $isUpdate 
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']
                : ['nullable', 'required_without:image_object', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }



    public function messages(): array
    {
        return [
            'link_up_event_id.required' => 'Choose Linkup Event',
            'code.required' => 'Coupon Code Is Required',
            'title.required' => 'Title Is Required',
            'description.required' => 'Description Is Required',
            'discount.required' => 'Discount Value Is Required',
            'discount.numeric' => 'Discount Must Be A Number',
            'discount.min' => 'Discount Cannot Be Negative',
            'expiry_date.required' => 'Please Choose Expiry Date',
            'expiry_date.date' => 'Invalid Date Format',
            'discount_type.required' => 'Discount Type Is Required',
            'status.required' => 'Status Is Required',
            'image_file.required_without' => 'Image Upload Is Mandatory',
        ];
    }
}
