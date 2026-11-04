<?php

namespace App\Actions;

use App\Jobs\SendShopCheckoutSellerEmailJob;
use App\Jobs\SendWhatsAppToSellerJob;
use App\Models\Notification;
use App\Models\Order;

/**
 * Tells each seller in a paid Marketplace order that they sold something (in-app notification,
 * email and WhatsApp, sent after the order commits). Used by checkout and by buying from a Vibe.
 */
class NotifyMarketplaceSellersAction
{
    public function execute(Order $order): void
    {
        $order->loadMissing([
            'customer',
            'orderItems.product.merchant.user',
            'orderItems.product.user',
        ]);

        $sellerUserIds = [];
        $sellerItemCounts = [];

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $sellerUserId = null;

            if ($product->merchant && $product->merchant->user) {
                $sellerUserId = (int) $product->merchant->user->id;
            } elseif ($product->user) {
                $sellerUserId = (int) $product->user->id;
            }

            if ($sellerUserId) {
                $sellerUserIds[$sellerUserId] = true;
                $sellerItemCounts[$sellerUserId] = ($sellerItemCounts[$sellerUserId] ?? 0) + (int) ($item->qty ?? 0);
            }
        }

        foreach (array_keys($sellerUserIds) as $sellerUserId) {
            $buyer = $order->customer;
            if ($buyer) {
                Notification::create([
                    'title' => 'Item sold',
                    'message' => 'You have a new Marketplace order '.($order->number ?? ('#'.$order->id)).'.',
                    'send_by' => (string) $buyer->id,
                    'user_id' => (int) $sellerUserId,
                    'type' => 'marketplace_order',
                    'context' => 'marketplace_orders',
                    'unread' => true,
                    'priority' => true,
                    'avatar' => $buyer->avatar ?? null,
                    'metadata' => [
                        'order_id' => (int) $order->id,
                        'order_number' => (string) ($order->number ?? ''),
                        'items_qty_total' => (int) ($sellerItemCounts[$sellerUserId] ?? 0),
                    ],
                ]);
            }

            SendShopCheckoutSellerEmailJob::dispatch((int) $order->id, (int) $sellerUserId)->afterCommit();
            SendWhatsAppToSellerJob::dispatch((int) $order->id, (int) $sellerUserId)->afterCommit();
        }
    }
}
