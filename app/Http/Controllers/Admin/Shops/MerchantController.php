<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Models\Merchants;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $paginator = Merchants::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('admin/shops/merchants/Index', [
            'paginator' => $paginator,
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'merchant_type' => 'required|in:store,group',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pickup_locations' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if (!empty($validated['pickup_locations'])) {
            $validated['pickup_locations'] = array_map('trim', explode(',', $validated['pickup_locations']));
        } else {
            $validated['pickup_locations'] = [];
        }

        Merchants::create($validated);

        return redirect()->back()->withSuccess('Merchant created successfully.');
    }

    public function update(Request $request, Merchants $merchant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'merchant_type' => 'required|in:store,group',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pickup_locations' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if (!empty($validated['pickup_locations'])) {
            $validated['pickup_locations'] = array_map('trim', explode(',', $validated['pickup_locations']));
        } else {
            $validated['pickup_locations'] = [];
        }

        $merchant->update($validated);

        return redirect()->back()->withSuccess('Merchant updated successfully.');
    }

    public function updateStatus(Request $request, Merchants $merchant)
    {
        $merchant->update([
            'is_active' => $request->is_active
        ]);

        return redirect()->back()->withSuccess('Merchant status updated.');
    }

    public function destroy(Merchants $merchant)
    {
        $merchant->delete();
        return redirect()->back()->withSuccess('Merchant deleted successfully.');
    }
}
