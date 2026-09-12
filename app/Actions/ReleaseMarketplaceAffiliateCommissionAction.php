<?php

namespace App\Actions;

use App\Models\MarketplaceAffiliateEarning;
use App\Models\Order;

class ReleaseMarketplaceAffiliateCommissionAction
{
    public function execute(Order $order): void
    {
        MarketplaceAffiliateEarning::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->update(['status' => 'available', 'released_at' => now(), 'updated_at' => now()]);
    }
}
