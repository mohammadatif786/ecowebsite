<?php

namespace App\Http\Controllers\Frontend\LinkUpShop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Jobs\SendShopCheckoutSellerEmailJob;
use App\Jobs\SendWhatsAppToSellerJob;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use App\Actions\CheckoutAction;
use App\DTOs\OrderTracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\OrderTrackingService;

class CheckOutController extends Controller
{
    public function __construct(private OrderTrackingService $trackingService) {}

    protected function dispatchSellerCheckoutEmails(Order $order): void
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
                    'message' => 'You have a new Marketplace order ' . ($order->number ?? ('#' . $order->id)) . '.',
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

            // Dispatch WhatsApp notification to seller
            SendWhatsAppToSellerJob::dispatch((int) $order->id, (int) $sellerUserId)->afterCommit();
        }
    }

    public function index()
    {
        return Inertia::render('User/LinkUpShop/Checkout/Index');
    }

    public function quote(Request $request, CheckoutAction $checkout)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'shipping_method' => ['required', 'in:standard,pickup'],
        ]);

        $pricing = app(\App\Services\CheckoutPricingService::class)->calculate(
            $data['items'],
            $request->only(['country', 'state', 'city', 'zip']),
            $data['shipping_method']
        );

        return response()->json($pricing);
    }
    public function store(StoreOrderRequest $request, CheckoutAction $checkout)
    {
        $payload = $checkout->handle($request->validated(), Auth::user());

        $pricing = $payload['pricing'];
        $user    = $payload['user'];
        $data    = $payload['dto'];

        // WALLET
        if ($data->paymentMethod === 'wallet') {

            if ($user->balance('USD')->value->get() < $pricing['net']) {
                return back()->withErrors(['balance' => 'Insufficient wallet balance']);
            }

            DB::transaction(function () use ($pricing, $data, $user) {

                $order = Order::create([
                    'user_id' => $user->id,
                    'number' => 'ORD-' . strtoupper(Str::random(10)),
                    'total' => $pricing['net'],
                    'subtotal_amount' => $pricing['subtotal'],
                    'tax_amount' => $pricing['tax'],
                    'shipping_amount' => $pricing['shipping'],
                    'discount_amount' => $pricing['discount'],
                    'fee_amount' => $pricing['fee_amount'] ?? 0,
                    'fee_label' => $pricing['fee_label'] ?? 'Marketplace Fee',
                    'process_fee_amount' => $pricing['process_fee_amount'] ?? 0,
                    'net_total' => $pricing['net'],
                    'street_address' => $data->address,
                    'city' => $data->city,
                    'state' => $data->state,
                    'country' => $data->country,
                    'zipcode' => $data->zip ?? null,
                    'status' => 'paid',
                    'shipping_method' => $data->shippingMethod,
                    'payment_method' => $data->paymentMethod,
                ]);


                foreach ($pricing['lines'] as $line) {
                    $order->orderItems()->create($line);

                    // Deplete product quantity
                    $product = Product::find($line['product_id']);
                    if ($product) {
                        $product->decrement('qty', $line['qty']);
                    }
                }

                app(\App\Actions\RecordMarketplaceAffiliateCommissionAction::class)
                    ->execute($order, session('marketplace.affiliate_promotion_id'));

                $this->trackingService->addTracking($order, new OrderTracking(
                    orderId: $order->id,
                    status: 'paid',
                    note: 'Order created and paid successfully',
                    meta: ['payment_method' => $data->paymentMethod],
                    lastUpdate: now()
                ));

                $this->dispatchSellerCheckoutEmails($order);

                transfer($pricing['net'], 'USD')
                    ->from($user)
                    ->to(custodian('e_money'))
                    ->commit();
            });

            return redirect()->route('new_frontend.marketplace')->withSuccess('Payment successful');
        }

        // CARD (Stripe)
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Order Payment'],
                    'unit_amount' => (int) ($pricing['net'] * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('frontend.product.oneTimePay.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('frontend.subscription.cancel'),
            'metadata' => [
                'user_id' => $user->id,
                'items' => json_encode($data->items),
                'address' => $data->address,
                'city' => $data->city,
                'state' => $data->state,
                'country' => $data->country,
                'zip' => $data->zip,
                'shipping_method' => $data->shippingMethod,
                'payment_method' => $data->paymentMethod,
            ]
        ]);

        return Inertia::location($session->url);
    }


    public function oneTimePaySuccess(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::retrieve($request->session_id);

        $user = User::findOrFail($session->metadata->user_id);

        $items = json_decode($session->metadata->items, true);

        $pricing = app(\App\Services\CheckoutPricingService::class)->calculate($items, [
            'country' => $session->metadata->country,
            'state' => $session->metadata->state,
            'city' => $session->metadata->city,
            'zip' => $session->metadata->zip,
        ], $session->metadata->shipping_method);

        DB::transaction(function () use ($session, $pricing, $user) {

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $pricing['net'],
                'subtotal_amount' => $pricing['subtotal'],
                'tax_amount' => $pricing['tax'],
                'shipping_amount' => $pricing['shipping'],
                'discount_amount' => $pricing['discount'],
                'fee_amount' => $pricing['fee_amount'] ?? 0,
                'fee_label' => $pricing['fee_label'] ?? 'Marketplace Fee',
                'process_fee_amount' => $pricing['process_fee_amount'] ?? 0,
                'net_total' => $pricing['net'],
                'street_address' => $session->metadata->address,
                'city' => $session->metadata->city,
                'state' => $session->metadata->state,
                'country' => $session->metadata->country,
                'status' => 'paid',
                'zipcode' => $session->metadata->zip ?? null,
                'shipping_method' => $session->metadata->shipping_method,
                'payment_method' => $session->metadata->payment_method,
            ]);

            foreach ($pricing['lines'] as $line) {
                $order->orderItems()->create($line);

                // Deplete product quantity
                $product = Product::find($line['product_id']);
                if ($product) {
                    $product->decrement('qty', $line['qty']);
                }
            }

            app(\App\Actions\RecordMarketplaceAffiliateCommissionAction::class)
                ->execute($order, session('marketplace.affiliate_promotion_id'));

            $this->trackingService->addTracking($order, new OrderTracking(
                orderId: $order->id,
                status: 'paid',
                note: 'Order created and paid successfully',
                meta: ['payment_method' => $session->metadata->payment_method],
                lastUpdate: now()
            ));

            $this->dispatchSellerCheckoutEmails($order);
        });

        return redirect()->route('new_frontend.marketplace')->withSuccess('Payment successful');
    }
}
