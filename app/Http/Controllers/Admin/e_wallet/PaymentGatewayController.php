<?php

namespace App\Http\Controllers\Admin\e_wallet;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\UserWalletKyc;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = Settings::whereIn('key', ['stripe', 'paypal'])->get()->keyBy('key');
        return Inertia::render('admin/e_wallet/CreatePaymentWay', compact('gateways'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeOrEditStripe(Request $request)
    {
        $existing = Settings::where('key', 'stripe')->first();

        $mergedValue = $existing
            ? array_merge($existing->value ?? [], $request->all())
            : $request->all();

        Settings::updateOrCreate(
            ['key' => 'stripe'],
            ['value' => $mergedValue]
        );

        return redirect()->route('admin.gateway.index')->with('message', 'Stripe settings updated successfully');
    }


    public function storeOrEditPaypal(Request $request)
    {
        $existing = Settings::where('key', 'paypal')->first();

        $mergedValue = $existing
            ? array_merge($existing->value ?? [], $request->all())
            : $request->all();

        Settings::updateOrCreate(
            ['key' => 'paypal'],
            ['value' => $mergedValue]
        );

        return redirect()->route('admin.gateway.index')->with('message', 'Stripe settings updated successfully');
    }
    
    public function userWalletKyc()
    {
        $walletKyc = UserWalletKyc::with('user')->latest()->paginate(10);
        return Inertia::render('admin/e_wallet/UserWalletKyc', [
            'walletKyc' => $walletKyc,
            'appURL' => config('app.url'),
        ]);
    }

    public function updateUserWalletKycStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $kyc = UserWalletKyc::findOrFail($id);
        $kyc->update(['status' => $request->status]);

        return back()->with('success', 'KYC status updated successfully.');
    }
}
