<?php

namespace App\Http\Controllers\Admin\e_wallet;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Balance;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use O21\LaravelWallet\Models\Custodian;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $digi_money = Settings::where('key', 'digimoney')
            ->value('value');
        $totalEmoney = Custodian::of('e_money')->balance('USD')->value->get();
        return Inertia::render('admin/e_wallet/Dashboard', [
            'unused_emoney' => $totalEmoney,
            // 'emoney' => $totalEmoney,
            'total_emoney' => $totalEmoney
        ]);
    }

    public function saveWalletSetting()
    {
        $walletsetting = Settings::where('key', 'walletSetting')->first();
        if ($walletsetting) {
            $walletsetting = json_decode($walletsetting->value);
        }
        return Inertia::render('admin/e_wallet/WalletSetting', [
            'walletsetting' => $walletsetting
        ]);
    }

    public function updateWalletSetting(Request $request)
    {
        $input = $request->all();

        Settings::updateOrCreate(['key' => 'walletSetting'], [
            'value' => json_encode($input, true)
        ]);

        return redirect()->route('admin.e.wallet.setting')->with('message', 'Advertisement created successfully.');
    }
}
