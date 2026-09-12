<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Settings;

class AppSettingController extends Controller
{
    public function index(Request $request)
    {
        $allKeys = ['eventFee', 'legal', 'support', 'contact_us', 'help', 'privacy_policy', 'community_guidelines', 'safety_center'];
        $alldescriptions = Settings::whereIn('key', $allKeys)->get();
        return Inertia::render('admin/settings/AppSetting', [
            'alldescriptions' => $alldescriptions
        ]);
    }

    public function saveSetting(Request $request)
    {
        //dd($request->all());
        foreach ($request->all() as $key => $page) {
            Settings::updateOrCreate(['key' => $key], ['value' => $page ?? '']);
        }

        return back()->with('success', 'App Setting Successfully Updated.');
    }

    public function eventFeeShow(Request $request)
    {
        $allKeys = ['eventFee', 'legal', 'support', 'contact_us', 'help', 'privacy_policy', 'community_guidelines', 'safety_center'];
        $alldescriptions = Settings::whereIn('key', $allKeys)->get();
        return Inertia::render('admin/events/Setting', [
            'alldescriptions' => $alldescriptions
        ]);
    }
}
