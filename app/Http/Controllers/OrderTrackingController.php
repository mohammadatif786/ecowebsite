<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderTrackingService;
use App\DTOs\OrderTracking;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function addStatus(Request $request, OrderTrackingService $service, $orderId)
    {
        $request->validate([
            'status' => 'required|string',
            'note'   => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($orderId);

        $tracking = new OrderTracking(
            orderId: $order->id,
            status: $request->status,
            note: $request->note ?? '',
            meta: [],
            lastUpdate: now(),
        );

        $service->addTracking($order, $tracking);

        return response()->json([
            'message' => 'Order status updated successfully'
        ]);
    }

    public function getStatus(OrderTrackingService $service, $orderId)
    {
        $order = Order::findOrFail($orderId);
        return response()->json($service->getOrderTracking($order));
    }
}
