<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\SellerOrderRepository;

class SellerOrderService
{
    public function __construct(
        private SellerOrderRepository $repo
    ) {}

    public function getPagedOrders(int $userId, array $filters = [])
    {
        return $this->repo->getSellerOrders($userId, $filters);
    }

    public function getOrderDetail(int $orderId, int $userId): Order
    {
        return $this->repo->getOrderWithBuyer($orderId, $userId);
    }

    public function updateStatus(int $orderId, int $userId, string $status): Order
    {
        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($status, $allowed)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        return $this->repo->updateOrderStatus($orderId, $userId, $status);
    }
}
