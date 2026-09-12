<?php

namespace App\Jobs;

use App\Mail\ShopSellerOrderPlacedMail;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendShopCheckoutSellerEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected int $orderId,
        protected int $sellerUserId,
    ) {
    }

    public function handle(): void
    {
        $emailLog = null;

        try {
            $order = Order::with([
                'customer',
                'orderItems.product.merchant.user',
                'orderItems.product.user',
            ])->find($this->orderId);

            if (! $order) {
                return;
            }

            $seller = User::find($this->sellerUserId);
            $buyer = $order->customer;

            if (! $seller || ! $buyer) {
                return;
            }

            $toEmail = (string) ($seller->email ?? '');

            if ($toEmail === '') {
                return;
            }

            $sellerItems = [];
            $pickupLocations = [];
            $hasPickup = false;

            foreach ($order->orderItems as $item) {
                $product = $item->product;
                if (! $product) {
                    continue;
                }

                $productSellerUserId = null;

                if ($product->merchant && $product->merchant->user) {
                    $productSellerUserId = (int) $product->merchant->user->id;
                } elseif ($product->user) {
                    $productSellerUserId = (int) $product->user->id;
                }

                if ($productSellerUserId !== (int) $seller->id) {
                    continue;
                }

                $listingType = (string) ($product->listing_type ?? '');
                if (strtolower($listingType) === 'pickup') {
                    $hasPickup = true;
                    $merchantPickup = $product->merchant?->pickup_locations;
                    if (is_array($merchantPickup)) {
                        foreach ($merchantPickup as $loc) {
                            if (is_string($loc) && trim($loc) !== '') {
                                $pickupLocations[] = trim($loc);
                            }
                        }
                    }
                }

                $sellerItems[] = [
                    'product_name' => (string) ($product->name ?? 'Product'),
                    'product_id' => (int) $product->id,
                    'quantity' => (int) ($item->qty ?? 0),
                    'unit_price' => (float) ($item->unit_price ?? 0),
                    'sub_total' => (float) ($item->sub_total ?? 0),
                    'listing_type' => $listingType,
                ];
            }

            if (count($sellerItems) === 0) {
                return;
            }

            $pickupLocations = array_values(array_unique($pickupLocations));

            $deliveryDetails = [
                'shipping_method' => (string) ($order->shipping_method ?? ''),
                'street_address' => (string) ($order->street_address ?? ''),
                'city' => (string) ($order->city ?? ''),
                'state' => (string) ($order->state ?? ''),
                'country' => (string) ($order->country ?? ''),
                'zipcode' => (string) ($order->zipcode ?? ''),
                'has_pickup' => $hasPickup,
                'pickup_locations' => $pickupLocations,
            ];

            $emailLog = EmailLog::create([
                'token' => (string) Str::uuid(),
                'email_type' => 'shop_seller_order_placed',
                'to_email' => $toEmail,
                'to_user_id' => (int) $seller->id,
                'from_user_id' => (int) $buyer->id,
                'subject' => 'New order placed: ' . ($order->number ?? ('Order #' . $order->id)),
                'status' => 'sending',
                'meta' => [
                    'order_id' => (int) $order->id,
                    'order_number' => (string) ($order->number ?? ''),
                    'seller_user_id' => (int) $seller->id,
                    'to_email' => $toEmail,
                    'items_count' => count($sellerItems),
                ],
            ]);

            Mail::to($toEmail)->send(new ShopSellerOrderPlacedMail(
                order: $order,
                seller: $seller,
                buyer: $buyer,
                sellerItems: $sellerItems,
                deliveryDetails: $deliveryDetails,
                trackingToken: (string) $emailLog->token,
            ));

            $emailLog->status = 'sent';
            $emailLog->sent_at = now();
            $emailLog->save();
        } catch (\Throwable $e) {
            if ($emailLog) {
                $emailLog->status = 'failed';
                $emailLog->error = $e->getMessage();
                $emailLog->save();
            }

            Log::warning('Failed to send shop seller checkout email', [
                'order_id' => $this->orderId,
                'seller_user_id' => $this->sellerUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
