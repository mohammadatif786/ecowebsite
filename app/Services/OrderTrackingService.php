<?php

namespace App\Services;

use App\DTOs\OrderTracking;
use App\Models\Order;
use App\Models\OrderTracking as OrderTrackingModel;

class OrderTrackingService
{
    public function addTracking(Order $order, OrderTracking $trackingDto): OrderTrackingModel
    {
        return $order->trackings()->create([
            'status'      => $trackingDto->status,
            'note'        => $trackingDto->note,
            'meta'        => $trackingDto->meta,
            'last_update' => $trackingDto->lastUpdate ?? now(),
        ]);
    }

    public function getOrderTracking(Order $order): array
    {
        return $order->trackings()->latest('last_update')->get()->map(function ($tracking) {
            return new OrderTracking(
                orderId: $tracking->order_id,
                status: $tracking->status,
                note: $tracking->note,
                meta: $tracking->meta,
                lastUpdate: $tracking->last_update->format('Y-m-d H:i:s')
            );
        })->toArray();
    }
}
