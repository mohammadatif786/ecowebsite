<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class AdsSettingRequest extends FormRequest
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
            'admob_banner_ad_id'=>'required|max:100',
            'admob_rewarded_video_id'=>'max:100',
            'admob_interstitial_ad_id'=>'max:100',
            'admob_native_ad_id'=>'max:100',
        ];
    }
}
