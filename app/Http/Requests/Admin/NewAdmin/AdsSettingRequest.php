<?php

namespace App\Http\Requests\Admin\NewAdmin;

use Illuminate\Foundation\Http\FormRequest;

class AdsSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admob_native_ad_id' => 'nullable|string|max:100',
            'admob_interstitial_ad_id' => 'nullable|string|max:100',
            'admob_banner_ad_id' => 'nullable|string|max:100',
            'admob_rewarded_video_id' => 'nullable|string|max:100',
            'home_feed_ads_enabled' => 'boolean',
            'marketplace_ads_enabled' => 'boolean',
            'news_sponsored_ads_enabled' => 'boolean',
            'live_stream_ads_enabled' => 'boolean',
            'event_ticket_ads_enabled' => 'boolean',
        ];
    }
}
