<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\AdsSettingRequest;
use App\Models\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdsSettingController extends Controller
{
    public function show(Settings $ads_setting)
    {
        return Inertia::render('admin/settings/adsSetting', [
            'adsSetting' => $ads_setting->value
        ]);
    }

    public function update(AdsSettingRequest $request, Settings $ads_setting)
    {

        $ads_setting->update([
            'value' => [
                'admob_banner_ad_id'=>$request->get('admob_banner_ad_id'),
                'admob_rewarded_video_id'=>$request->get('admob_rewarded_video_id'),
                'admob_interstitial_ad_id'=>$request->get('admob_interstitial_ad_id'),
                'admob_native_ad_id'=>$request->get('admob_native_ad_id'),
            ]
        ]);
        $ads_setting->save();
        return redirect()->back()->withSuccess('Ads Setting values updated');
    }
}
