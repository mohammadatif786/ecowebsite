<?php

namespace App\Services;

use App\Actions\NotifyMarketplaceSellersAction;
use App\Actions\RecordMarketplaceAffiliateCommissionAction;
use App\DTOs\OrderTracking;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vibe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Exception\InsufficientFundsException;

/**
 * Buying the Marketplace item tagged on a Vibe, paid from the wallet. The server prices the
 * order (same pricing as Marketplace checkout), creates a real order for the seller, takes
 * the money once, and credits the poster's commission. The client never sends a price.
 */
class VibeShopService
{
    public const MAX_QTY = 10;

    public function __construct(
        private readonly CheckoutPricingService $pricing,
        private readonly OrderTrackingService $tracking,
        private readonly RecordMarketplaceAffiliateCommissionAction $commissions,
        private readonly NotifyMarketplaceSellersAction $notifySellers,
    ) {}

    /**
     * What it would cost: { product, qty, shipping_method, subtotal, tax, shipping, fee_amount,
     * fee_label, total, wallet_balance, can_afford, pickup, commission_to }.
     *
     * @param  array{product_id:int, qty?:int, shipping_method?:string, country?:?string, state?:?string, city?:?string, zip?:?string}  $data
     */
    public function quote(User $buyer, Vibe $vibe, array $data): array
    {
        $product = $this->taggedProduct($vibe, (int) $data['product_id']);
        $qty = $this->qty($data);
        $this->assertCanBuy($buyer, $product, $qty);

        $pricing = $this->price($product, $qty, $data);
        $balance = $this->walletBalance($buyer);

        return [
            'product' => [
                'id' => $product->id,
                'title' => $product->name,
                'price' => (float) $product->price,
                'image' => \App\Support\VibeFeedPresenter::image($product->cover_image) ?? $product->image_url,
                'seller' => $product->user?->name ?? 'Marketplace',
                'in_stock' => (int) $product->qty,
            ],
            'qty' => $qty,
            'shipping_method' => $data['shipping_method'] ?? 'standard',
            'subtotal' => round((float) $pricing['subtotal'], 2),
            'tax' => (float) $pricing['tax'],
            'shipping' => (float) $pricing['shipping'],
            'fee_amount' => (float) $pricing['fee_amount'],
            'fee_label' => $pricing['fee_label'],
            'total' => (float) $pricing['net'],
            'wallet_balance' => $balance,
            'can_afford' => $balance >= (float) $pricing['net'],
            'pickup' => $this->pickupLocation($product),
            'commission_to' => $this->affiliateFor($buyer, $vibe, $product)?->name,
        ];
    }

    /**
     * Pay from the wallet and place the order. Returns { order_id, order_number, total, wallet_balance }.
     *
     * @param  array{product_id:int, qty?:int, shipping_method?:string, address?:?string, country?:?string, state?:?string, city?:?string, zip?:?string, expected_total?:mixed}  $data
     */
    public function buy(User $buyer, Vibe $vibe, array $data): array
    {
        $product = $this->taggedProduct($vibe, (int) $data['product_id']);
        $qty = $this->qty($data);
        $method = $data['shipping_method'] ?? 'standard';

        if ($method === 'standard') {
            foreach (['address' => 'Add a delivery address.', 'city' => 'Add your city.', 'country' => 'Add your country.'] as $field => $message) {
                if (blank($data[$field] ?? null)) {
                    throw ValidationException::withMessages([$field => $message]);
                }
            }
        }

        $affiliate = $this->affiliateFor($buyer, $vibe, $product);

        try {
            $order = DB::transaction(function () use ($buyer, $vibe, $product, $qty, $method, $data, $affiliate) {
                // Lock the item so two buyers can't take the last one.
                $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
                $this->assertCanBuy($buyer, $locked, $qty);

                $pricing = $this->price($locked, $qty, $data);

                // The buyer confirmed a total; if the price changed since, ask again.
                if (isset($data['expected_total']) && is_numeric($data['expected_total'])
                    && abs((float) $data['expected_total'] - (float) $pricing['net']) > 0.009) {
                    throw ValidationException::withMessages(['total' => 'The price changed to $'.number_format((float) $pricing['net'], 2).'. Check the total and try again.']);
                }

                $order = Order::create([
                    'user_id' => $buyer->id,
                    'number' => 'ORD-'.strtoupper(Str::random(10)),
                    'total' => $pricing['net'],
                    'subtotal_amount' => $pricing['subtotal'],
                    'tax_amount' => $pricing['tax'],
                    'shipping_amount' => $pricing['shipping'],
                    'discount_amount' => $pricing['discount'],
                    'fee_amount' => $pricing['fee_amount'] ?? 0,
                    'fee_label' => $pricing['fee_label'] ?? 'Marketplace Fee',
                    'process_fee_amount' => $pricing['process_fee_amount'] ?? 0,
                    'net_total' => $pricing['net'],
                    'street_address' => $method === 'pickup' ? 'Pickup from seller' : trim((string) $data['address']),
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'country' => $data['country'] ?? null,
                    'zipcode' => $data['zip'] ?? null,
                    'status' => 'paid',
                    'shipping_method' => $method,
                    'payment_method' => 'wallet',
                ]);

                foreach ($pricing['lines'] as $line) {
                    $order->orderItems()->create($line);
                }
                $locked->decrement('qty', $qty);

                if ($affiliate) {
                    $this->commissions->execute($order, null, $affiliate->id, $vibe->id);
                }

                $this->tracking->addTracking($order, new OrderTracking(
                    orderId: $order->id,
                    status: 'paid',
                    note: 'Order created and paid from wallet (bought from a Vibe)',
                    meta: ['payment_method' => 'wallet', 'vibe_id' => $vibe->id],
                    lastUpdate: now()
                ));

                if ((float) $pricing['net'] > 0) {
                    transfer($pricing['net'], 'USD')
                        ->from($buyer)
                        ->to(custodian('e_money'))
                        ->overcharge(false)
                        ->meta(['note' => "Order {$order->number} · bought from Vibe #{$vibe->id}"])
                        ->commit();
                }

                $this->notifySellers->execute($order);

                return $order;
            });
        } catch (InsufficientFundsException) {
            throw ValidationException::withMessages(['wallet' => 'Insufficient wallet balance — top up first.']);
        }

        return [
            'order_id' => $order->id,
            'order_number' => $order->number,
            'total' => (float) $order->net_total,
            'wallet_balance' => $this->walletBalance($buyer),
            'message' => "Bought {$product->name} · 🔒 escrow-protected",
        ];
    }

    /** The product must be tagged on this post — a post can't be used to buy anything else. */
    public function taggedProduct(Vibe $vibe, int $productId): Product
    {
        $product = $vibe->products()->with('user')->where('products.id', $productId)->first();

        if (! $product) {
            throw ValidationException::withMessages(['product_id' => 'That item isn’t tagged on this post.']);
        }

        return $product;
    }

    private function assertCanBuy(User $buyer, Product $product, int $qty): void
    {
        if (! $product->status) {
            throw ValidationException::withMessages(['product_id' => 'This item is no longer for sale.']);
        }
        if ((int) $product->user_id === (int) $buyer->id) {
            throw ValidationException::withMessages(['product_id' => 'That’s your own item.']);
        }
        if ((int) $product->qty < $qty) {
            throw ValidationException::withMessages(['qty' => (int) $product->qty > 0 ? 'Only '.(int) $product->qty.' left.' : 'Sold out.']);
        }
    }

    private function price(Product $product, int $qty, array $data): array
    {
        return $this->pricing->calculate(
            [['id' => $product->id, 'qty' => $qty]],
            ['country' => $data['country'] ?? null, 'state' => $data['state'] ?? null, 'city' => $data['city'] ?? null, 'zip' => $data['zip'] ?? null],
            ($data['shipping_method'] ?? 'standard') === 'pickup' ? 'pickup' : 'standard'
        );
    }

    /** Who earns the commission: the person who posted — never the buyer or the seller themself. */
    private function affiliateFor(User $buyer, Vibe $vibe, Product $product): ?User
    {
        $poster = (int) $vibe->created_by;
        if ($poster === (int) $buyer->id || $poster === (int) $product->user_id) {
            return null;
        }
        if ($product->commMode === 'none' || ($product->commMode === null)) {
            return null;
        }

        return User::find($poster);
    }

    private function qty(array $data): int
    {
        $qty = (int) ($data['qty'] ?? 1);
        if ($qty < 1 || $qty > self::MAX_QTY) {
            throw ValidationException::withMessages(['qty' => 'Choose 1 to '.self::MAX_QTY.'.']);
        }

        return $qty;
    }

    private function pickupLocation(Product $product): ?string
    {
        $seller = $product->user;
        if (! $seller) {
            return null;
        }
        $parts = array_filter([$seller->new_city ?: $seller->city, $seller->new_state ?: $seller->state, $seller->new_country ?: $seller->country]);

        return $parts ? implode(', ', $parts) : null;
    }

    private function walletBalance(User $user): float
    {
        return round((float) ($user->balance('USD')->value->get() ?? 0), 2);
    }
}
