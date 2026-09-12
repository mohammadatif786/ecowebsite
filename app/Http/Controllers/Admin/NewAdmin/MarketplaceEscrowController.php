<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceEscrowController extends Controller
{
    public function index(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        // Use the same logic as OrderController@escrowReleases
        $paginator = Order::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->with(['customer', 'items.product.seller'])
            ->paginate(15)
            ->withQueryString();

        $totalEscrowVolume = Order::sum('total');
        $pendingEscrow = Order::whereNotIn('status', ['completed', 'received_buyer', 'delivered', 'cancelled', 'refunded'])->sum('total');
        $releasedEscrow = Order::whereIn('status', ['completed', 'received_buyer', 'delivered'])->sum('total');
        $disputesCount = Order::where('status', 'disputed')->count();

        // Format orders to match the MarketplaceEscrow.vue expectations
        $formattedItems = collect($paginator->items())->map(function ($order) {
            $firstItem = $order->items->first();
            $sellerName = $firstItem?->product?->seller?->name ?? 'N/A';
            $fee = $order->fee_amount ?? 0;
            $net = $order->total - $fee;

            $isReleased = in_array($order->status, ['completed', 'received_buyer', 'delivered']);

            return [
                'id' => $order->number,
                'raw_id' => $order->id,
                'seller' => $sellerName,
                'gross' => (float)$order->total,
                'fee' => (float)$fee,
                'net' => (float)$net,
                'status' => $isReleased ? 'Released' : 'Held',
                'raw_status' => $order->status
            ];
        });

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceEscrow', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'escrowItems' => [
                'data' => $formattedItems,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                    'links' => $paginator->linkCollection()->toArray()
                ]
            ],
            'stats' => [
                'pending' => (float)$pendingEscrow,
                'released' => (float)$releasedEscrow,
                'disputes' => $disputesCount
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function release(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Logic to release funds: update order status to 'delivered'
        // and handle any associated WithdrawRequest if necessary.
        $order->update(['status' => 'delivered']);

        // Check if there's a pending withdraw request for this order and approve it
        WithdrawRequest::where('order_id', $order->id)
            ->where('request_status', 'pending')
            ->update([
                'request_status' => 'approved',
                'payment_status' => 'paid'
            ]);

        return redirect()->back()->with('message', 'Funds released successfully');
    }
}
