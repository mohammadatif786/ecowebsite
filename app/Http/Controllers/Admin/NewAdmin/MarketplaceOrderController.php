<?php

namespace App\Http\Controllers\Admin\NewAdmin;

use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceOrderController extends Controller
{
    public function index(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        // Fetch orders with related data
        // We'll group by region and country in the frontend as in the original template
        $orders = Order::with(['customer', 'items.product.seller'])
            ->when($request->search, function ($q, $search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Format orders for the frontend view
        $formattedOrders = collect($orders->items())->map(function ($order) {
            // Find the primary seller from the first item
            $firstItem = $order->items->first();
            $sellerName = $firstItem?->product?->seller?->name ?? 'Mixed Sellers';

            // Map status to match the frontend colors/labels if needed
            // original labels: Paid, Delivered, Buyer Received, Shipped, Disputed, Refunded
            // Escrow: Held, Released, Refunded

            // Infer region based on country (same logic as GetAdminOverviewDataAction if possible)
            $region = $this->inferRegion($order->country ?? 'Unknown');

            return [
                'id' => $order->number,
                'customer' => $order->customer?->name ?? 'Guest',
                'email' => $order->customer?->email ?? '—',
                'seller' => $sellerName,
                'region' => $region,
                'country' => $order->country ?? 'Unknown',
                'city' => $order->city ?? '—',
                'total' => (float)$order->total,
                'payment' => $order->payment_method ?? 'Card',
                'status' => ucfirst($order->status ?? 'Paid'),
                'escrow' => $order->status === 'delivered' ? 'Released' : 'Held', // Simple logic
                'date' => $order->created_at->format('M j, Y'),
                'raw_id' => $order->id
            ];
        });

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceOrders', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'orders' => [
                'data' => $formattedOrders,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total(),
                    'links' => $orders->linkCollection()->toArray()
                ]
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Order $order, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        $order->load(['customer', 'items.product.seller', 'trackings']);

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceOrderView', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('message', 'Order status updated successfully');
    }

    private function inferRegion(string $country): string
    {
        $caribbean = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Guyana', 'Dominican Republic', 'Haiti', 'Saint Lucia', 'Grenada', 'Antigua & Barbuda'];
        if (in_array($country, $caribbean)) {
            return 'Caribbean';
        }
        return 'Local'; // Defaulting to Local for simplicity as per original template
    }
}
