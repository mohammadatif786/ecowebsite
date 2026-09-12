<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Models\ShopFee;
use App\Models\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceSettingController extends Controller
{
    public function index(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        // Fetch commission/fee data from shop_fees
        // In this implementation, we assume there's one primary fee record for marketplace
        $shopFee = ShopFee::first();

        // Fetch escrow rules from global settings
        $escrowSettings = Settings::where('key', 'marketplaceEscrowSettings')->first()?->value;

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceFees', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'shopFee' => $shopFee,
            'escrowSettings' => $escrowSettings ?? [
                'release_trigger' => 'Release after delivered',
                'hold_period' => '3 days after delivery'
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'commission' => 'required|numeric|min:0',
            'processing_fee' => 'required|numeric|min:0',
            'fixed_fee' => 'required|numeric|min:0',
            'release_trigger' => 'required|string',
            'hold_period' => 'required|string',
        ]);

        // Update ShopFee
        $shopFee = ShopFee::first() ?? new ShopFee();
        $shopFee->percent = $data['commission'];
        $shopFee->fixed = $data['fixed_fee'];
        // Note: processing_fee could be stored in a separate column if added,
        // or combined. For now we use percent for commission.
        $shopFee->save();

        // Update Escrow in global settings
        $settings = Settings::firstOrNew(['key' => 'marketplaceEscrowSettings']);
        $settings->value = [
            'release_trigger' => $data['release_trigger'],
            'hold_period' => $data['hold_period'],
            'processing_fee' => $data['processing_fee'], // Storing it here for simplicity
        ];
        $settings->save();

        return redirect()->back()->with('message', 'Marketplace fees updated successfully');
    }
}
