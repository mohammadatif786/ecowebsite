<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SMTPSettingRequest;
use App\Models\Settings;
use Inertia\Inertia;

class SmtpSettingController extends Controller
{
    public function show(Settings $smtp_setting){
        return Inertia::render('admin/settings/smtpSetting',[
            'smtpSetting'=> $smtp_setting->value
        ]);
    }

    public function update(SMTPSettingRequest $request, Settings $smtp_setting) {
        $smtp_setting->update([
            'value'=> [
                'host' => $request->get('host'),
                'port' => $request->get('port'),
                'encryption' => $request->get('encryption'),
                'username' => $request->get('username'),
                'password' => $request->get('password'),
            ]
        ]);
        $smtp_setting->save();
        return redirect()->back()->withSuccess('SMTP Setting values updated');
    }
}
