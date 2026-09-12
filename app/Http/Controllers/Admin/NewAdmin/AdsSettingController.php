<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewAdmin\AdsSettingRequest;
use App\Models\Settings;

class AdsSettingController extends Controller
{
    public function update(AdsSettingRequest $request)
    {
        $settings = Settings::firstOrCreate(['key' => 'adsSettings'], ['value' => []]);
        $settings->update([
            'value' => array_merge($settings->value ?? [], $request->validated()),
        ]);

        return redirect()->back()->with('message', 'Ads settings updated successfully.');
    }
}
