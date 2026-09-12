<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Models\GiftPurchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminGiftPurchases extends Controller
{
    public function index(Request $request)
    {
        $paginator = GiftPurchase::query()
            ->with(['gift', 'user'])
            ->search($request->get('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/shops/purchaseGifts/Index', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
        ]);
    }

    public function destroy(GiftPurchase $gift)
    {
        $gift->delete();
        return redirect()->back()->withSuccess('Gift Histroy Deleted Successfully');
    }
}
