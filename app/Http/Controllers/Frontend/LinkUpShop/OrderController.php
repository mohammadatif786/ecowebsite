<?php

namespace App\Http\Controllers\Frontend\LinkUpShop;

use App\Http\Controllers\Controller;
use App\DTOs\OrderTracking;
use App\Services\OrderTrackingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items.product.merchant', 'trackings'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $latest = $order->trackings->sortByDesc('created_at')->first();
                return [
                    'id'       => $order->id,
                    'no'       => $order->number,
                    'date'     => $order->created_at->toDateTimeString(),
                    'shipping_method' => $order->shipping_method,
                    'items'    => $order->items->map(function ($item) {
                        return [
                            'id'        => $item->id,
                            'quantity'  => $item->qty,
                            'unitPrice' => $item->unit_price,
                            'subTotal'  => $item->sub_total,
                            'product'   => $item->product?->name,
                            'listing_type' => $item->product?->listing_type,
                            'seller'    => $item->product?->merchant?->name,
                            'image'     => $item->product?->cover_image,
                        ];
                    }),
                    'total'    => $order->total,
                    'subtotal' => $order->subtotal_amount,
                    'fee_amount' => $order->fee_amount,
                    'fee_label'  => $order->fee_label,
                    'process_fee_amount' => $order->process_fee_amount,
                    'currency' => 'USD',
                    'ship'     => [
                        'name'    => $order->user?->name,
                        'address' => $order->street_address,
                        'city'    => $order->city,
                    ],
                    'payment'  => $order->payment_method ?? 'card',
                    'status'   => $order->status,
                    'latest_tracking' => $latest ? [
                        'status' => $latest->status,
                        'note'   => $latest->note,
                        'date'   => $latest->created_at?->toDateTimeString(),
                    ] : null,
                    'trackings' => $order->trackings->sortByDesc('created_at')->values()->map(function ($t) {
                        return [
                            'status' => $t->status,
                            'note'   => $t->note,
                            'date'   => $t->created_at?->toDateTimeString(),
                            'meta'   => $t->meta ?? null,
                        ];
                    }),
                ];
            });

        if ($request->wantsJson()) {
            return response()->json(['orders' => $orders], 200);
        }

        return Inertia::render('User/LinkUpShop/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function buyerReceived(Request $request, OrderTrackingService $trackingService, Order $order)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'received' => ['required', 'boolean'],
            'note' => ['required', 'string', 'max:500'],
        ]);

        if ($order->status !== 'delivered') {
            return response()->json([
                'message' => 'Order is not delivered yet.'
            ], 422);
        }

        $trackingStatus = $validated['received'] ? 'buyer_received' : 'buyer_not_received';
        $orderStatus = $validated['received'] ? 'received_buyer' : 'not_received_buyer';

        if ($validated['received']) {
            app(\App\Actions\ReleaseMarketplaceAffiliateCommissionAction::class)->execute($order);
        }

        $trackingService->addTracking($order, new OrderTracking(
            orderId: (string) $order->id,
            status: $trackingStatus,
            note: $validated['note'],
            meta: [
                'received' => (bool) $validated['received'],
                'by' => 'buyer',
            ],
            lastUpdate: now(),
        ));

        $order->status = $orderStatus;
        $order->save();

        return response()->json([
            'message' => 'Confirmation saved',
            'order_status' => $order->status,
            'tracking_status' => $trackingStatus,
        ], 200);
    }
}
