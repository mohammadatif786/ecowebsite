<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'required|string|in:restaurant,club,general',
            'advertiser_name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'headline' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'ad_type' => 'required|string|in:image,video,both',
            'image' => 'nullable|string',
            'video' => 'nullable|string', // Max ~2MB for base64 encoded video
            'thumbnail' => 'nullable|string',
            'max_duration' => 'nullable|string|max:50',
            'autoplay_sound' => 'nullable|string',
            'cta_overlay_timing' => 'nullable|string|in:start,5,10,end',
            'loop_video' => 'nullable|string|in:yes,no',
            'cta_text' => 'nullable|string|max:100',
            'brand_color' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration_days' => 'nullable|integer|min:0',
            'price_package' => 'nullable|string|in:starter,growth,network',
            'custom_cost_override' => 'nullable|numeric|min:0',
            'payment_ref' => 'nullable|string|max:100',
            'is_paid' => 'boolean',
            'publication_status' => 'required|string|max:100',
            'targeting_notes' => 'nullable|string',
        ];
    }
}
