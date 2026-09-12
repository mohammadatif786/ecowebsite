<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\TicketSaleAddon;
use App\Models\LinkUpEvent;
use App\Models\EventFeeSetting;
use App\Services\TaxRateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BuyTicketEmailMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Stripe\Stripe;
use O21\LaravelWallet\Facades\Wallet;

class TicketPurchaseController extends Controller
{
    /**
     * Buy ticket using wallet balance
     * Complex version matching Frontend\WalletController functionality
     */
    public function buyTicketWallet(Request $request)
    {
        try {
            $this->normalizeJsonCartPayload($request);
            $user = Auth::user();
            $validated = $request->validate($this->cartValidationRules(true));
            $items = $this->normalizedCartItems($validated['items'] ?? []);

            if (empty($items)) {
                return back()->withErrors(['items' => 'Please select at least one ticket']);
            }

            $tickets = Ticket::with(['event', 'event.eventDetails', 'extraSetting'])
                ->whereIn('id', collect($items)->pluck('ticketId')->all())
                ->get()
                ->keyBy('id');

            $lineItemsTotal = 0;
            foreach ($items as $item) {
                $ticket = $tickets->get((int) $item['ticketId']);
                if (!$ticket) {
                    return back()->withErrors(['items' => 'Ticket not found']);
                }

                $quantity = (int) $item['qty'];
                $availabilityError = $this->ticketAvailabilityError($ticket, $quantity);
                if ($availabilityError) {
                    return back()->withErrors(['error' => $availabilityError]);
                }

                $price = $this->discountedTicketPrice($ticket);
                $lineItemsTotal += $this->ticketSubtotal($ticket, $quantity, $price);
                $lineItemsTotal += $this->addonTotal($item['addons'] ?? []);
                $lineItemsTotal += $this->wellnessTotal($ticket, $item['wellness'] ?? [], $quantity);
                $lineItemsTotal += $this->cookoutTotal($ticket, $item['cookout'] ?? []);
            }

            $checkoutSummary = $validated['checkoutSummary'] ?? [];
            $orderFeesTotal = (float) ($checkoutSummary['ticketProcessingFees'] ?? 0);
            $orderTaxTotal = (float) ($checkoutSummary['tax'] ?? 0);
            $summaryTotal = (float) ($checkoutSummary['total'] ?? 0);
            $walletChargeTotal = $summaryTotal > 0 ? $summaryTotal : ($lineItemsTotal + $orderFeesTotal + $orderTaxTotal);

            $walletBalance = $user->balance('USD')->value->get();
            if ($walletBalance < $walletChargeTotal) {
                return back()->withErrors(['wallet' => 'Insufficient wallet balance']);
            }

            $walletOrderId = 'WALLET-' . (string) Str::uuid();
            transfer($walletChargeTotal, 'USD')->from($user)->to(custodian('e_money'))->commit();

            DB::beginTransaction();

            $tickets = Ticket::with(['event', 'event.eventDetails', 'extraSetting'])
                ->whereIn('id', collect($items)->pluck('ticketId')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $isFirstSale = true;
            foreach ($items as $item) {
                $ticket = $tickets->get((int) $item['ticketId']);
                if (!$ticket) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Ticket not found']);
                }

                $quantity = (int) $item['qty'];
                $availabilityError = $this->ticketAvailabilityError($ticket, $quantity);
                if ($availabilityError) {
                    DB::rollBack();
                    return back()->withErrors(['error' => $availabilityError]);
                }

                $price = $this->discountedTicketPrice($ticket);
                $ticketSubtotal = $this->ticketSubtotal($ticket, $quantity, $price);
                $tablesTotal = $this->packageTableTotal($ticket, $quantity, $price);
                $addons = $item['addons'] ?? [];
                $wellness = $item['wellness'] ?? [];
                $cookout = $item['cookout'] ?? [];
                $addonsTotal = $this->addonTotal($addons);
                $wellnessTotal = $this->wellnessTotal($ticket, $wellness, $quantity);
                $cookoutTotal = $this->cookoutTotal($ticket, $cookout);
                $lineTotal = $ticketSubtotal + $addonsTotal + $wellnessTotal + $cookoutTotal;
                $saleFees = $isFirstSale ? $orderFeesTotal : 0;
                $saleTax = $isFirstSale ? $orderTaxTotal : 0;

                // Calculate discount for this sale record
                $saleDiscount = 0;
                if ($isFirstSale) {
                    $appliedCoupons = $validated['appliedCoupons'] ?? [];
                    foreach ($appliedCoupons as $coupon) {
                        if (($coupon['discountType'] ?? 'amount') === 'percentage') {
                            $saleDiscount += ($lineItemsTotal * ((float) ($coupon['discount'] ?? 0) / 100));
                        } else {
                            $saleDiscount += (float) ($coupon['discount'] ?? 0);
                        }
                    }
                    $saleDiscount = min($saleDiscount, $lineItemsTotal);
                }

                $saleTotal = $lineTotal + $saleFees + $saleTax - $saleDiscount;

                $ticketSale = TicketSale::create([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'ticket_name' => $ticket->name,
                    'ticket_type' => $ticket->ticket_type,
                    'ticket_qrcode_id' => Str::random(20),
                    'ticket_qrcode' => Str::random(20),
                    'link_up_event_id' => $ticket->event_id,
                    'quantity' => $quantity,
                    'total_price' => $saleTotal,
                    'no_of_tickets' => $quantity,
                    'sub_total' => $ticketSubtotal,
                    'total' => $saleTotal,
                    'fee' => $saleFees,
                    'discount' => $saleDiscount,
                    'coupan_amount' => $saleDiscount,
                    'event_tax' => $saleTax,
                    'stripe_price' => $saleTotal,
                    'fee_breakdown' => $isFirstSale ? $this->checkoutFeeBreakdown($checkoutSummary) : null,
                    'tables_total' => $tablesTotal,
                    'drinks_total' => $addonsTotal,
                    'drink_addons' => $this->addonPayload($addons),
                    'wellness_total' => $wellnessTotal,
                    'wellness_addons' => $this->wellnessPayload($ticket, $wellness),
                    'cookout_total' => $cookoutTotal,
                    'cookout_included_protein' => $cookout['includedProtein'] ?? null,
                    'cookout_included_sides' => $this->cookoutIncludedSides($ticket),
                    'cookout_addons' => $this->cookoutPayload($ticket, $cookout),
                    'status' => 'completed',
                    'ticket_status' => 'confirmed',
                    'payment_method' => 'wallet',
                    'pay_type' => 'Wallet',
                    'stripe_id' => $walletOrderId,
                    'stripe_status' => 'paid',
                ]);

                $this->createAddonRows($ticketSale, $addons);
                $this->createWellnessAddonRows($ticketSale, $ticket, $wellness);
                $this->createCookoutAddonRows($ticketSale, $ticket, $cookout);

                $ticket->quantity -= $quantity;
                $ticket->save();
                $isFirstSale = false;
            }

            DB::commit();

            return back()->withSuccess('Ticket purchased successfully using wallet');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Wallet ticket purchase failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to purchase ticket. Please try again.']);
        }
    }

    /**
     * Handle simple wallet purchase (backward compatibility)
     */
    private function handleSimpleWalletPurchase(Request $request)
    {
        $this->normalizeJsonCartPayload($request);

        $validated = $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'quantity' => 'required|integer|min:1',
            'addons' => 'array|nullable',
            'addons.*.price' => 'sometimes|numeric|min:0',
            'wellness' => 'array|nullable',
        ]);

        $user = Auth::user();
        $ticket = Ticket::with(['event', 'event.eventDetails'])->findOrFail($validated['ticket_id']);

        $ticketPrice = $this->discountedTicketPrice($ticket);
        $ticketSubtotal = $this->ticketSubtotal($ticket, (int) $validated['quantity'], $ticketPrice);
        $tablesTotal = $this->packageTableTotal($ticket, (int) $validated['quantity'], $ticketPrice);
        $addonsTotal = $this->addonTotal($validated['addons'] ?? []);
        $wellnessTotal = $this->wellnessTotal($ticket, $validated['wellness'] ?? [], (int) $validated['quantity']);
        $totalPrice = $ticketSubtotal + $addonsTotal + $wellnessTotal;

        // Wallet balance check using Laravel Wallet package
        $walletBalance = $user->balance('USD')->value->get();
        if ($walletBalance < $totalPrice) {
            return back()->withErrors(['wallet' => 'Insufficient wallet balance']);
        }

        $availabilityError = $this->ticketAvailabilityError($ticket, (int) $validated['quantity']);
        if ($availabilityError) {
            return back()->withErrors(['error' => $availabilityError]);
        }

        try {
            // Deduct from wallet using Laravel Wallet package
            transfer($totalPrice, 'USD')->from($user)->to(custodian('e_money'))->commit();

            DB::beginTransaction();

            $ticketSale = TicketSale::create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'link_up_event_id' => $ticket->event_id,
                'ticket_name' => $ticket->name,
                'ticket_qrcode_id' => Str::random(20),
                'ticket_qrcode' => '', // Placeholder - QR code generation would go here
                'quantity' => $validated['quantity'],
                'total_price' => $totalPrice,
                'no_of_tickets' => $validated['quantity'],
                'sub_total' => $ticketSubtotal,
                'total' => $totalPrice,
                'tables_total' => $tablesTotal,
                'drinks_total' => $addonsTotal,
                'drink_addons' => $this->addonPayload($validated['addons'] ?? []),
                'wellness_total' => $wellnessTotal,
                'wellness_addons' => $this->wellnessPayload($ticket, $validated['wellness'] ?? []),
                'status' => 'completed',
                'ticket_status' => 'confirmed',
                'payment_method' => 'wallet',
                'pay_type' => 'Wallet',
            ]);

            $ticket->quantity -= $validated['quantity'];
            $ticket->save();

            if (!empty($validated['addons'])) {
                foreach ($validated['addons'] as $addon) {
                    TicketSaleAddon::create([
                        'ticket_sale_id' => $ticketSale->id,
                        'addon_name' => $addon['name'] ?? 'Addon',
                        'quantity' => $addon['quantity'] ?? 1,
                        'unit_price' => $addon['price'] ?? 0,
                        'total_price' => ($addon['price'] ?? 0) * ($addon['quantity'] ?? 1),
                        'category' => $addon['category'] ?? 'general',
                    ]);
                }
            }

            $this->createWellnessAddonRows($ticketSale, $ticket, $validated['wellness'] ?? []);

            DB::commit();

            return back()->withSuccess('Ticket purchased successfully using wallet');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Simple wallet purchase failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to purchase ticket. Please try again.']);
        }
    }

    /**
     * Buy ticket using Stripe
     * Simplified version for new frontend - handles basic ticket purchases
     * For complex cart items (drinks, cookout, wellness), use the existing Frontend\StripeController
     */
    public function buyTicketStripe(Request $request)
    {
        try {
            $this->normalizeJsonCartPayload($request);
            $user = Auth::user();
            $validated = $request->validate($this->cartValidationRules(true));
            $items = $this->normalizedCartItems($validated['items'] ?? []);
            if (empty($items)) {
                return back()->withErrors(['items' => 'Please select at least one ticket']);
            }

            $tickets = Ticket::with(['event', 'event.eventDetails', 'extraSetting'])
                ->whereIn('id', collect($items)->pluck('ticketId')->all())
                ->get()
                ->keyBy('id');

            $totalPrice = 0;
            $firstTicket = null;
            $lineItems = [];
            foreach ($items as $item) {
                $ticket = $tickets->get((int) $item['ticketId']);
                if (!$ticket) {
                    return back()->withErrors(['items' => 'Ticket not found']);
                }
                $firstTicket ??= $ticket;

                $availabilityError = $this->ticketAvailabilityError($ticket, (int) $item['qty']);
                if ($availabilityError) {
                    return back()->withErrors(['error' => $availabilityError]);
                }

                $price = $this->discountedTicketPrice($ticket);
                $lineTotal = $this->ticketSubtotal($ticket, (int) $item['qty'], $price);
                $lineTotal += $this->addonTotal($item['addons'] ?? []);
                $lineTotal += $this->wellnessTotal($ticket, $item['wellness'] ?? [], (int) $item['qty']);
                $lineTotal += $this->cookoutTotal($ticket, $item['cookout'] ?? []);
                $totalPrice += $lineTotal;

                if ($lineTotal > 0) {
                    $lineItems[] = $this->stripeLineItem(
                        $ticket->name ?: 'Event Ticket',
                        $lineTotal,
                        $this->stripeItemDescription($ticket, $item)
                    );
                }
            }

            $checkoutSummary = $validated['checkoutSummary'] ?? [];
            $ticketProcessingFees = (float) ($checkoutSummary['ticketProcessingFees'] ?? 0);
            $tax = (float) ($checkoutSummary['tax'] ?? 0);
            $summaryTotal = (float) ($checkoutSummary['total'] ?? 0);

            // Calculate total discount from applied coupons
            $appliedCoupons = $validated['appliedCoupons'] ?? [];
            $totalDiscount = 0;
            foreach ($appliedCoupons as $coupon) {
                if (($coupon['discountType'] ?? 'amount') === 'percentage') {
                    $totalDiscount += ($totalPrice * ((float) ($coupon['discount'] ?? 0) / 100));
                } else {
                    $totalDiscount += (float) ($coupon['discount'] ?? 0);
                }
            }
            $totalDiscount = min($totalDiscount, $totalPrice);

            $stripeTotal = $summaryTotal > 0 ? $summaryTotal : ($totalPrice - $totalDiscount + $ticketProcessingFees + $tax);
            $adjustment = round($stripeTotal - ($totalPrice - $totalDiscount) - $ticketProcessingFees - $tax, 2);

            if ($ticketProcessingFees > 0) {
                $lineItems[] = $this->stripeLineItem('Ticket & Processing Fees', $ticketProcessingFees, 'Service and processing fees');
            }

            if ($tax > 0) {
                $lineItems[] = $this->stripeLineItem('Tax', $tax, 'Estimated tax');
            }

            if ($adjustment > 0) {
                $lineItems[] = $this->stripeLineItem('Order Adjustment', $adjustment, 'Order total adjustment');
            }

            if (empty($lineItems)) {
                return back()->withErrors(['items' => 'Unable to create checkout for an empty cart']);
            }

            $cartToken = (string) Str::uuid();
            Cache::put($this->stripeCartCacheKey($cartToken), [
                'user_id' => $user->id,
                'items' => $items,
                'appliedCoupons' => $appliedCoupons,
                'checkoutSummary' => $checkoutSummary,
            ], now()->addHours(3));

            // Create Stripe checkout session
            Stripe::setApiKey(config('services.stripe.secret'));

            $sessionParams = [
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('new_frontend.stripe.buy-ticket.success') .
                    '?session_id={CHECKOUT_SESSION_ID}&cart_token=' . urlencode($cartToken),
                'cancel_url' => route('new_frontend.events'),
                'metadata' => [
                    'user_id' => $user->id,
                    'cart_token' => $cartToken,
                ],
            ];

            if ($totalDiscount > 0) {
                try {
                    $stripeCoupon = \Stripe\Coupon::create([
                        'amount_off' => (int) round($totalDiscount * 100),
                        'currency' => 'usd',
                        'duration' => 'once',
                        'name' => 'Coupon Discount',
                    ]);
                    $sessionParams['discounts'] = [['coupon' => $stripeCoupon->id]];
                } catch (\Exception $e) {
                    Log::warning('Failed to create Stripe coupon: ' . $e->getMessage());
                }
            }

            $session = \Stripe\Checkout\Session::create($sessionParams);

            return redirect()->away($session->url);

        } catch (\Exception $e) {
            Log::error('Stripe ticket purchase failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to initiate Stripe payment. Please try again.']);
        }
    }

    /**
     * Handle Stripe success callback
     */
    public function stripeSuccess(Request $request)
    {
        try {
            $sessionId = $request->query('session_id');

            if (!$sessionId || $sessionId === '{CHECKOUT_SESSION_ID}') {
                return redirect()->route('new_frontend.events')->withErrors(['error' => 'Invalid payment session']);
            }

            // Verify Stripe session
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('new_frontend.events')->withErrors(['error' => 'Payment not completed']);
            }

            $user = Auth::user();
            $cartToken = $request->query('cart_token') ?: ($session->metadata->cart_token ?? null);
            $cartPayload = $cartToken ? Cache::get($this->stripeCartCacheKey($cartToken)) : null;

            if (!$cartPayload || (int) ($cartPayload['user_id'] ?? 0) !== (int) $user->id || empty($cartPayload['items'])) {
                return redirect()->route('new_frontend.events')->withErrors(['error' => 'Payment cart expired. Please contact support.']);
            }

            $items = $this->normalizedCartItems($cartPayload['items']);
            $appliedCoupons = $cartPayload['appliedCoupons'] ?? [];
            $checkoutSummary = is_array($cartPayload['checkoutSummary'] ?? null) ? $cartPayload['checkoutSummary'] : [];
            $orderFeesTotal = (float) ($checkoutSummary['ticketProcessingFees'] ?? 0);
            $orderTaxTotal = (float) ($checkoutSummary['tax'] ?? 0);
            $isFirstSale = true;

            $tickets = Ticket::with(['event', 'event.eventDetails', 'extraSetting'])
                ->whereIn('id', collect($items)->pluck('ticketId')->all())
                ->get()
                ->keyBy('id');

            // Calculate total discount for DB storage
            $totalOrderDiscount = 0;
            $itemsBaseTotal = 0;
            foreach ($items as $item) {
                $ticket = $tickets->get((int) $item['ticketId']);
                if ($ticket) {
                    $price = $this->discountedTicketPrice($ticket);
                    $itemsBaseTotal += $this->ticketSubtotal($ticket, (int) $item['qty'], $price);
                    $itemsBaseTotal += $this->addonTotal($item['addons'] ?? []);
                    $itemsBaseTotal += $this->wellnessTotal($ticket, $item['wellness'] ?? [], (int) $item['qty']);
                    $itemsBaseTotal += $this->cookoutTotal($ticket, $item['cookout'] ?? []);
                }
            }

            foreach ($appliedCoupons as $coupon) {
                if (($coupon['discountType'] ?? 'amount') === 'percentage') {
                    $totalOrderDiscount += ($itemsBaseTotal * ((float) ($coupon['discount'] ?? 0) / 100));
                } else {
                    $totalOrderDiscount += (float) ($coupon['discount'] ?? 0);
                }
            }
            $totalOrderDiscount = min($totalOrderDiscount, $itemsBaseTotal);

            DB::beginTransaction();
            // Re-fetch with lock for update
            $tickets = Ticket::with(['event', 'event.eventDetails', 'extraSetting'])
                ->whereIn('id', collect($items)->pluck('ticketId')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $ticket = $tickets->get((int) $item['ticketId']);
                if (!$ticket) {
                    DB::rollBack();
                    return redirect()->route('new_frontend.events')->withErrors(['error' => 'Ticket not found']);
                }

                $quantity = (int) $item['qty'];
                $availabilityError = $this->ticketAvailabilityError($ticket, $quantity);
                if ($availabilityError) {
                    DB::rollBack();
                    return redirect()->route('new_frontend.events')->withErrors(['error' => $availabilityError]);
                }

                $price = $this->discountedTicketPrice($ticket);
                $ticketSubtotal = $this->ticketSubtotal($ticket, $quantity, $price);
                $tablesTotal = $this->packageTableTotal($ticket, $quantity, $price);
                $addons = $item['addons'] ?? [];
                $wellness = $item['wellness'] ?? [];
                $cookout = $item['cookout'] ?? [];
                $addonsTotal = $this->addonTotal($addons);
                $wellnessTotal = $this->wellnessTotal($ticket, $wellness, $quantity);
                $cookoutTotal = $this->cookoutTotal($ticket, $cookout);
                $lineTotal = $ticketSubtotal + $addonsTotal + $wellnessTotal + $cookoutTotal;
                $saleFees = $isFirstSale ? $orderFeesTotal : 0;
                $saleTax = $isFirstSale ? $orderTaxTotal : 0;
                $saleDiscount = $isFirstSale ? $totalOrderDiscount : 0;
                $saleTotal = $lineTotal + $saleFees + $saleTax - $saleDiscount;

                $ticketSale = TicketSale::create([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'ticket_name' => $ticket->name,
                    'ticket_type' => $ticket->ticket_type,
                    'ticket_qrcode_id' => Str::random(20),
                    'ticket_qrcode' => Str::random(20),
                    'link_up_event_id' => $ticket->event_id,
                    'quantity' => $quantity,
                    'total_price' => $saleTotal,
                    'no_of_tickets' => $quantity,
                    'sub_total' => $ticketSubtotal,
                    'total' => $saleTotal,
                    'fee' => $saleFees,
                    'discount' => $saleDiscount,
                    'coupan_amount' => $saleDiscount,
                    'event_tax' => $saleTax,
                    'stripe_price' => $saleTotal,
                    'fee_breakdown' => $isFirstSale ? $this->checkoutFeeBreakdown($checkoutSummary) : null,
                    'tables_total' => $tablesTotal,
                    'drinks_total' => $addonsTotal,
                    'drink_addons' => $this->addonPayload($addons),
                    'wellness_total' => $wellnessTotal,
                    'wellness_addons' => $this->wellnessPayload($ticket, $wellness),
                    'cookout_total' => $cookoutTotal,
                    'cookout_included_protein' => $cookout['includedProtein'] ?? null,
                    'cookout_included_sides' => $this->cookoutIncludedSides($ticket),
                    'cookout_addons' => $this->cookoutPayload($ticket, $cookout),
                    'status' => 'completed',
                    'ticket_status' => 'confirmed',
                    'payment_method' => 'stripe',
                    'pay_type' => 'Stripe',
                    'stripe_session_id' => $sessionId,
                    'stripe_id' => $sessionId,
                    'stripe_status' => 'paid',
                ]);

                $this->createAddonRows($ticketSale, $addons);
                $this->createWellnessAddonRows($ticketSale, $ticket, $wellness);
                $this->createCookoutAddonRows($ticketSale, $ticket, $cookout);

                $ticket->quantity -= $quantity;
                $ticket->save();
                $isFirstSale = false;
            }

            DB::commit();
            if ($cartToken) {
                Cache::forget($this->stripeCartCacheKey($cartToken));
            }

            return redirect()->route('new_frontend.events')->withSuccess('Ticket purchased successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe success callback failed: ' . $e->getMessage());
            return redirect()->route('new_frontend.events')->withErrors(['error' => 'Failed to complete ticket purchase. Please contact support.']);
        }
    }

    private function discountedTicketPrice(Ticket $ticket): float
    {
        $base = (float) ($ticket->price ?? 0);
        $promo = (float) ($ticket->promo_price ?? 0);

        return max(0, $base - $promo);
    }

    private function ticketSubtotal(Ticket $ticket, int $quantity, float $discountedPrice): float
    {
        return ($discountedPrice * $quantity) + $this->packageTableTotal($ticket, $quantity, $discountedPrice);
    }

    private function packageTableTotal(Ticket $ticket, int $quantity, float $discountedPrice): float
    {
        $hasTable = $this->isEnabledValue($ticket->has_table ?? null);
        $tablePrice = (float) ($ticket->table_price ?? 0);

        if (!$ticket->package_id || !$hasTable || $tablePrice <= 0 || $tablePrice === $discountedPrice) {
            return 0;
        }

        return $tablePrice * $quantity;
    }

    private function isEnabledValue($value): bool
    {
        return $value === true || $value === 'yes' || $value === 1 || $value === '1';
    }

    private function wellnessConfig(Ticket $ticket): ?array
    {
        $config = $ticket->wellness ?? null;
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            $config = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return is_array($config) && ($config['includeService'] ?? null) === 'yes' ? $config : null;
    }

    private function wellnessTotal(Ticket $ticket, array $selection, int $ticketQty): float
    {
        $total = 0;
        foreach ($this->wellnessPayload($ticket, $selection) ?? [] as $addon) {
            $total += (float) ($addon['total_price'] ?? 0);
        }

        $config = $this->wellnessConfig($ticket);
        if ($config && ($selection['serviceMode'] ?? 'inhouse') === 'mobile') {
            $total += ((float) ($config['booking']['mobileFee'] ?? 0)) * max(1, $ticketQty);
        }

        return $total;
    }

    private function wellnessPayload(Ticket $ticket, array $selection): ?array
    {
        $config = $this->wellnessConfig($ticket);
        if (!$config) {
            return null;
        }

        $payload = [];
        $serviceLookup = collect($config['services'] ?? [])->keyBy(fn ($item) => strtolower(trim((string) ($item['name'] ?? ''))));
        $manualLookup = collect($config['manualAddons'] ?? [])->keyBy(fn ($item) => strtolower(trim((string) ($item['name'] ?? ''))));

        if (!empty($selection['selectedSlot'])) {
            $payload[] = [
                'name' => 'Selected Slot: ' . $selection['selectedSlot'],
                'category' => 'wellness_slot',
                'quantity' => 1,
                'unit_price' => 0,
                'total_price' => 0,
                'slot' => $selection['selectedSlot'],
                'slot_date' => $selection['selectedSlotDate'] ?? null,
            ];
        }

        $mode = $selection['serviceMode'] ?? 'inhouse';
        $payload[] = [
            'name' => 'Service mode: ' . ($mode === 'mobile' ? 'Mobile' : 'In-house'),
            'category' => 'wellness_mode',
            'quantity' => 1,
            'unit_price' => 0,
            'total_price' => 0,
        ];

        if ($mode === 'mobile' && !empty($selection['contactPhone'])) {
            $payload[] = [
                'name' => 'Contact phone: ' . $selection['contactPhone'],
                'category' => 'wellness_contact_phone',
                'quantity' => 1,
                'unit_price' => 0,
                'total_price' => 0,
            ];
        }

        if (!empty($selection['includedService'])) {
            $payload[] = [
                'name' => $selection['includedService'],
                'category' => 'wellness_included_service',
                'quantity' => 1,
                'unit_price' => 0,
                'total_price' => 0,
            ];
        }

        foreach ($selection['services'] ?? [] as $service) {
            $name = trim((string) ($service['name'] ?? ''));
            $qty = (int) ($service['qty'] ?? 0);
            if ($name === '' || $qty <= 0) {
                continue;
            }
            $matched = $serviceLookup->get(strtolower($name));
            $unitPrice = (float) ($matched['price'] ?? 0);
            $payload[] = [
                'name' => $matched['name'] ?? $name,
                'category' => 'wellness_service',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $qty,
            ];
        }

        foreach ($selection['manualAddons'] ?? [] as $addon) {
            $name = trim((string) ($addon['name'] ?? ''));
            $qty = (int) ($addon['qty'] ?? 0);
            if ($name === '' || $qty <= 0) {
                continue;
            }
            $matched = $manualLookup->get(strtolower($name));
            $unitPrice = (float) ($matched['price'] ?? 0);
            $payload[] = [
                'name' => $matched['name'] ?? $name,
                'category' => 'wellness_manual',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $qty,
            ];
        }

        return empty($payload) ? null : $payload;
    }

    private function createWellnessAddonRows(TicketSale $ticketSale, Ticket $ticket, array $selection): void
    {
        foreach ($this->wellnessPayload($ticket, $selection) ?? [] as $addon) {
            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => $addon['name'] ?? 'Wellness Add-on',
                'quantity' => $addon['quantity'] ?? 1,
                'unit_price' => $addon['unit_price'] ?? 0,
                'total_price' => $addon['total_price'] ?? 0,
                'category' => $addon['category'] ?? 'wellness',
            ]);
        }
    }

    private function createAddonRows(TicketSale $ticketSale, array $addons): void
    {
        foreach ($addons as $addon) {
            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => $addon['name'] ?? 'Addon',
                'quantity' => $addon['quantity'] ?? 1,
                'unit_price' => $addon['price'] ?? 0,
                'total_price' => ($addon['price'] ?? 0) * ($addon['quantity'] ?? 1),
                'category' => $addon['category'] ?? 'general',
            ]);
        }
    }

    private function createCookoutAddonRows(TicketSale $ticketSale, Ticket $ticket, array $selection): void
    {
        foreach ($this->cookoutPayload($ticket, $selection) ?? [] as $addon) {
            TicketSaleAddon::create([
                'ticket_sale_id' => $ticketSale->id,
                'addon_name' => $addon['name'] ?? 'Cookout Add-on',
                'quantity' => $addon['quantity'] ?? 1,
                'unit_price' => $addon['unit_price'] ?? 0,
                'total_price' => $addon['total_price'] ?? 0,
                'category' => $addon['category'] ?? 'cookout',
            ]);
        }
    }

    private function cookoutConfig(Ticket $ticket): ?array
    {
        $config = $ticket->cookout ?? null;
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            $config = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return is_array($config) && ($config['includeFood'] ?? null) === 'yes' ? $config : null;
    }

    private function cookoutIncludedSides(Ticket $ticket): ?array
    {
        $sides = $this->cookoutConfig($ticket)['sides'] ?? [];
        return is_array($sides) && !empty($sides) ? array_values($sides) : null;
    }

    private function cookoutOptionLookup(Ticket $ticket, string $group): \Illuminate\Support\Collection
    {
        $config = $this->cookoutConfig($ticket) ?? [];
        $items = [];

        if ($group === 'proteins') {
            $items = array_merge($config['proteins'] ?? [], $config['customProteins'] ?? []);
        } elseif ($group === 'drinks') {
            $items = $config['drinks'] ?? [];
        } elseif ($group === 'manualAddons') {
            $items = $config['manualAddons'] ?? [];
        }

        return collect($items)->map(function ($item) {
            if (is_string($item)) {
                return ['name' => $item, 'price' => 0, 'mode' => 'included'];
            }

            return is_array($item) ? $item : [];
        })->filter(fn ($item) => !empty($item['name']))
            ->keyBy(fn ($item) => strtolower(trim((string) $item['name'])));
    }

    private function cookoutPayload(Ticket $ticket, array $selection): ?array
    {
        if (!$this->cookoutConfig($ticket)) {
            return null;
        }

        $payload = [];
        $groups = [
            'proteins' => 'cookout_protein',
            'drinks' => 'cookout_drink',
            'manualAddons' => 'cookout_manual',
        ];

        foreach ($groups as $selectionKey => $category) {
            $lookup = $this->cookoutOptionLookup($ticket, $selectionKey);
            foreach ($selection[$selectionKey] ?? [] as $addon) {
                $name = trim((string) ($addon['name'] ?? ''));
                $qty = (int) ($addon['qty'] ?? 0);
                if ($name === '' || $qty <= 0) {
                    continue;
                }

                $matched = $lookup->get(strtolower($name));
                $unitPrice = (float) ($matched['price'] ?? 0);
                $payload[] = [
                    'name' => $matched['name'] ?? $name,
                    'category' => $category,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $qty,
                ];
            }
        }

        return empty($payload) ? null : $payload;
    }

    private function cookoutTotal(Ticket $ticket, array $selection): float
    {
        return collect($this->cookoutPayload($ticket, $selection) ?? [])
            ->sum(fn ($addon) => (float) ($addon['total_price'] ?? 0));
    }

    private function stripeLineItem(string $name, float $amount, ?string $description = null): array
    {
        $productData = ['name' => $name];
        if ($description) {
            $productData['description'] = Str::limit($description, 450, '...');
        }

        return [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => $productData,
                'unit_amount' => max(0, (int) round($amount * 100)),
            ],
            'quantity' => 1,
        ];
    }

    private function stripeItemDescription(Ticket $ticket, array $item): string
    {
        $parts = [];
        $eventTitle = $ticket->event?->title;
        if ($eventTitle) {
            $parts[] = $eventTitle;
        }

        $parts[] = 'Quantity: ' . (int) ($item['qty'] ?? 1);

        foreach ($item['addons'] ?? [] as $addon) {
            $name = trim((string) ($addon['name'] ?? ''));
            $qty = (int) ($addon['quantity'] ?? 0);
            if ($name !== '' && $qty > 0) {
                $parts[] = "{$name} x{$qty}";
            }
        }

        $cookout = $item['cookout'] ?? [];
        if (!empty($cookout['includedProtein'])) {
            $parts[] = 'Cookout plate: ' . $cookout['includedProtein'];
        }

        foreach (['proteins', 'drinks', 'manualAddons'] as $group) {
            foreach ($cookout[$group] ?? [] as $addon) {
                $name = trim((string) ($addon['name'] ?? ''));
                $qty = (int) ($addon['qty'] ?? 0);
                if ($name !== '' && $qty > 0) {
                    $parts[] = "{$name} x{$qty}";
                }
            }
        }

        $wellness = $item['wellness'] ?? [];
        if (!empty($wellness['serviceMode'])) {
            $parts[] = 'Service mode: ' . ($wellness['serviceMode'] === 'mobile' ? 'Mobile' : 'In-house');
        }
        if (!empty($wellness['selectedSlot'])) {
            $parts[] = 'Slot: ' . $wellness['selectedSlot'];
        }
        if (!empty($wellness['includedService'])) {
            $parts[] = 'Included service: ' . $wellness['includedService'];
        }

        foreach (['services', 'manualAddons'] as $group) {
            foreach ($wellness[$group] ?? [] as $addon) {
                $name = trim((string) ($addon['name'] ?? ''));
                $qty = (int) ($addon['qty'] ?? 0);
                if ($name !== '' && $qty > 0) {
                    $parts[] = "{$name} x{$qty}";
                }
            }
        }

        return implode(' | ', $parts);
    }

    private function checkoutFeeBreakdown(array $summary): ?array
    {
        $fees = is_array($summary['fees'] ?? null) ? $summary['fees'] : [];
        $breakdown = [
            'service_fee_pct_amount' => round((float) ($fees['serviceFeePercent'] ?? 0), 2),
            'service_fee_fixed_amount' => round((float) ($fees['serviceFeeFixed'] ?? 0), 2),
            'processing_fee_pct_amount' => round((float) ($fees['processingFeePercent'] ?? 0), 2),
            'processing_fee_fixed_amount' => round((float) ($fees['processingFeeFixed'] ?? 0), 2),
            'drink_fee_amount' => round((float) ($fees['drinkFees'] ?? 0), 2),
            'bottle_fee_amount' => round((float) ($fees['bottleFee'] ?? 0), 2),
            'vip_package_fee_amount' => round((float) ($fees['vipPackageFeePct'] ?? 0), 2),
            'mobile_fee_amount' => round((float) ($fees['mobileFee'] ?? 0), 2),
            'tax_total' => round((float) ($summary['tax'] ?? 0), 2),
            'services_subtotal' => round((float) ($summary['servicesSubtotal'] ?? 0), 2),
            'ticket_processing_fees' => round((float) ($summary['ticketProcessingFees'] ?? 0), 2),
            'order_total' => round((float) ($summary['total'] ?? 0), 2),
        ];

        return array_filter($breakdown, fn ($value) => abs((float) $value) > 0);
    }

    private function addonTotal(array $addons): float
    {
        return collect($addons)->sum(function ($addon) {
            return ((float) ($addon['price'] ?? 0)) * ((int) ($addon['quantity'] ?? 1));
        });
    }

    private function addonPayload(array $addons): ?array
    {
        $payload = collect($addons)->map(function ($addon) {
            $quantity = (int) ($addon['quantity'] ?? 1);
            $unitPrice = (float) ($addon['price'] ?? 0);

            return [
                'addon_id' => $addon['addon_id'] ?? null,
                'name' => $addon['name'] ?? 'Addon',
                'category' => $addon['category'] ?? 'general',
                'section' => $addon['section'] ?? '-',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
            ];
        })->values()->all();

        return empty($payload) ? null : $payload;
    }

    private function cartValidationRules(bool $itemsRequired = false): array
    {
        return [
            'items' => ($itemsRequired ? 'required' : 'sometimes') . '|array|min:1',
            'items.*.ticketId' => 'required|integer|exists:tickets,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.addons' => 'sometimes|array|nullable',
            'items.*.addons.*.addon_id' => 'sometimes|integer|nullable',
            'items.*.addons.*.name' => 'sometimes|string|nullable',
            'items.*.addons.*.quantity' => 'integer',
            'items.*.addons.*.price' => 'sometimes|numeric|min:0',
            'items.*.addons.*.category' => 'sometimes|string|nullable',
            'items.*.addons.*.section' => 'sometimes|string|nullable',
            'items.*.cookout' => 'sometimes|array|nullable',
            'items.*.cookout.includedProtein' => 'sometimes|string|nullable',
            'items.*.cookout.proteins' => 'sometimes|array|nullable',
            'items.*.cookout.proteins.*.name' => 'required_with:items.*.cookout.proteins|string',
            'items.*.cookout.proteins.*.qty' => 'required_with:items.*.cookout.proteins|integer|min:0',
            'items.*.cookout.drinks' => 'sometimes|array|nullable',
            'items.*.cookout.drinks.*.name' => 'required_with:items.*.cookout.drinks|string',
            'items.*.cookout.drinks.*.qty' => 'required_with:items.*.cookout.drinks|integer|min:0',
            'items.*.cookout.manualAddons' => 'sometimes|array|nullable',
            'items.*.cookout.manualAddons.*.name' => 'required_with:items.*.cookout.manualAddons|string',
            'items.*.cookout.manualAddons.*.qty' => 'required_with:items.*.cookout.manualAddons|integer|min:0',
            'items.*.wellness' => 'sometimes|array|nullable',
            'items.*.wellness.selectedSlot' => 'sometimes|string|nullable',
            'items.*.wellness.selectedSlotDate' => 'sometimes|string|nullable',
            'items.*.wellness.holdExpiresAt' => 'sometimes|numeric|nullable',
            'items.*.wellness.includedService' => 'sometimes|string|nullable',
            'items.*.wellness.serviceMode' => 'sometimes|string|in:inhouse,mobile',
            'items.*.wellness.contactPhone' => 'sometimes|string|nullable',
            'items.*.wellness.services' => 'sometimes|array|nullable',
            'items.*.wellness.services.*.name' => 'required_with:items.*.wellness.services|string',
            'items.*.wellness.services.*.qty' => 'required_with:items.*.wellness.services|integer|min:0',
            'items.*.wellness.manualAddons' => 'sometimes|array|nullable',
            'items.*.wellness.manualAddons.*.name' => 'required_with:items.*.wellness.manualAddons|string',
            'items.*.wellness.manualAddons.*.qty' => 'required_with:items.*.wellness.manualAddons|integer|min:0',
            'appliedCoupons' => 'sometimes|array|nullable',
            'checkoutSummary' => 'sometimes|array|nullable',
            'checkoutSummary.servicesSubtotal' => 'sometimes|numeric|min:0',
            'checkoutSummary.ticketProcessingFees' => 'sometimes|numeric|min:0',
            'checkoutSummary.tax' => 'sometimes|numeric|min:0',
            'checkoutSummary.total' => 'sometimes|numeric|min:0',
            'checkoutSummary.fees' => 'sometimes|array|nullable',
        ];
    }

    private function normalizedCartItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'ticketId' => (int) ($item['ticketId'] ?? 0),
                'qty' => (int) ($item['qty'] ?? 0),
                'addons' => array_values($item['addons'] ?? []),
                'cookout' => $item['cookout'] ?? [],
                'wellness' => $item['wellness'] ?? [],
            ];
        })->filter(fn ($item) => $item['ticketId'] > 0 && $item['qty'] > 0)
            ->values()
            ->all();
    }

    private function stripeCartCacheKey(string $cartToken): string
    {
        return "new_frontend_stripe_cart:{$cartToken}";
    }

    private function normalizeJsonCartPayload(Request $request): void
    {
        foreach (['items', 'appliedCoupons', 'addons', 'checkoutSummary'] as $key) {
            $value = $request->input($key);

            if (!is_string($value)) {
                continue;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge([$key => $decoded]);
            }
        }
    }

    private function ticketAvailabilityError(Ticket $ticket, int $requestedQty): ?string
    {
        if ($requestedQty < 1) {
            return 'Select at least one ticket';
        }

        $available = (int) ($ticket->quantity ?? 0);
        $ticketName = $ticket->name ?: 'Ticket';
        $salesEnd = $ticket->sale_end ?? $ticket->sales_end ?? null;

        if ($salesEnd) {
            try {
                if (Carbon::parse($salesEnd)->isPast()) {
                    return "{$ticketName} is no longer available";
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to parse ticket sales end date', [
                    'ticket_id' => $ticket->id,
                    'sales_end' => $salesEnd,
                ]);
            }
        }

        if ($available <= 0) {
            return "{$ticketName} is sold out";
        }

        if ($available < $requestedQty) {
            return "Only {$available} {$ticketName} ticket(s) available";
        }

        return null;
    }
}
