<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SellerOrderRepository
{
    /**
     * Paginated orders that contain at least one product belonging to this seller.
     */
    public function getSellerOrders(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Order::query()
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $userId))
            ->with([
                'customer:id,name,email,avatar',
                'items' => fn ($q) => $q->whereHas('product', fn ($p) => $p->where('user_id', $userId)),
                'items.product:id,name,cover_image,price,user_id',
            ])
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where('number', 'like', "%{$s}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $s) =>
                $q->where('status', $s)
            )
            ->latest();

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Single order detail with buyer info, guarded to seller's items only.
     */
    public function getOrderWithBuyer(int $orderId, int $userId): ?Order
    {
        return Order::with([
            'customer:id,name,email,avatar',
            'items' => fn ($q) => $q->whereHas('product', fn ($p) => $p->where('user_id', $userId)),
            'items.product:id,name,cover_image,price,user_id',
            'trackings',
        ])
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $userId))
            ->findOrFail($orderId);
    }

    /**
     * Update order status (only if seller owns items in this order).
     */
    public function updateOrderStatus(int $orderId, int $userId, string $status): Order
    {
        $order = $this->getOrderWithBuyer($orderId, $userId);
        $order->update(['status' => $status]);
        return $order->fresh();
    }
}
