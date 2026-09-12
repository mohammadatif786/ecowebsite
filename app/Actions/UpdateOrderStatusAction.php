<?php

namespace App\Actions;

use App\Models\Order;
use App\Services\SellerOrderService;

class UpdateOrderStatusAction
{
    public function __construct(
        private SellerOrderService $service
    ) {}

    public function execute(int $orderId, int $userId, string $status): Order
    {
        return $this->service->updateStatus($orderId, $userId, $status);
    }
}
