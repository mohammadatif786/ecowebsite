<?php

namespace App\Actions;

use App\Models\MarketplaceAffiliateEarning;
use App\Models\MarketplaceAffiliatePromotion;
use App\Models\Order;

class RecordMarketplaceAffiliateCommissionAction
{
    public function execute(Order $order, ?int $promotionId): void
    {
        if (! $promotionId) return;
        $promotion = MarketplaceAffiliatePromotion::query()->find($promotionId);
        if (! $promotion) return;

        $order->loadMissing('orderItems.product');
        foreach ($order->orderItems as $item) {
            if ((int) $item->product_id !== (int) $promotion->product_id) continue;
            $product = $item->product;
            if (! $product || $product->commMode === 'none') continue;
            $amount = $product->commMode === 'flat'
                ? (float) $product->commFlat * (int) $item->qty
                : (float) $item->sub_total * ((float) $product->commission / 100);
            if ($amount <= 0) continue;
            MarketplaceAffiliateEarning::firstOrCreate([
                'promotion_id' => $promotion->id,
                'order_item_id' => $item->id,
            ], [
                'affiliate_user_id' => $promotion->user_id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'commission_amount' => round($amount, 2),
                'status' => 'pending',
            ]);
        }
    }
}
