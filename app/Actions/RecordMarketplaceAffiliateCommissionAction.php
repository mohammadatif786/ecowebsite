<?php

namespace App\Actions;

use App\Models\MarketplaceAffiliateEarning;
use App\Models\MarketplaceAffiliatePromotion;
use App\Models\Order;
use App\Models\Vibe;

class RecordMarketplaceAffiliateCommissionAction
{
    public function execute(Order $order, ?int $promotionId = null, ?int $affiliateUserId = null, ?int $vibeId = null): void
    {
        $order->loadMissing('orderItems.product');

        $baseAffiliateUserId = $affiliateUserId;
        if (! $baseAffiliateUserId && $promotionId) {
            $promotion = MarketplaceAffiliatePromotion::query()->find($promotionId);
            if ($promotion) {
                $baseAffiliateUserId = $promotion->user_id;
            }
        }
        if (! $baseAffiliateUserId && $vibeId) {
            $vibe = Vibe::find($vibeId);
            if ($vibe) {
                $baseAffiliateUserId = $vibe->created_by;
            }
        }

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            if (! $product || $product->commMode === 'none') continue;

            $affUserId = $baseAffiliateUserId;
            $itemMeta = is_array($item->meta) ? $item->meta : json_decode($item->meta ?? '{}', true);
            $itemAffId = $itemMeta['affiliate_user_id'] ?? null;
            $itemVibeId = $itemMeta['vibe_id'] ?? null;

            if ($itemAffId) {
                $affUserId = (int) $itemAffId;
            } elseif ($itemVibeId) {
                $vibe = Vibe::find($itemVibeId);
                if ($vibe) {
                    $affUserId = $vibe->created_by;
                }
            }

            if (! $affUserId) continue;

            $promotion = MarketplaceAffiliatePromotion::firstOrCreate([
                'user_id' => $affUserId,
                'product_id' => $item->product_id,
            ]);

            $amount = $product->commMode === 'flat'
                ? (float) $product->commFlat * (int) $item->qty
                : (float) $item->sub_total * ((float) $product->commission / 100);

            if ($amount <= 0) continue;

            MarketplaceAffiliateEarning::firstOrCreate([
                'promotion_id' => $promotion->id,
                'order_item_id' => $item->id,
            ], [
                'affiliate_user_id' => $affUserId,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'commission_amount' => round($amount, 2),
                'status' => 'pending',
            ]);
        }
    }
}
