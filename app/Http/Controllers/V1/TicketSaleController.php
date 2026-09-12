<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\Settings;
use App\Models\Ticket;
use App\Http\Requests\V1\TicketCardRequest;
use App\Models\Tax;
use App\Models\TicketSale;
use App\Models\User;
use App\Services\PopularityScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class TicketSaleController extends Controller
{
    public $country;
    public function __construct(Request $request)
    {
        $this->country = $request->header('country') ?? "";
    }
    // for getting all booking of the user 
    public function getAllBooking(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:all,pending,canceled,confirmed'
        ]);

        if ($request->type == 'all') {
            $bookings = TicketSale::where('user_id', Auth::user()->id)->with(['event', 'ticket', 'user'])->get();
        } else {
            $bookings = TicketSale::where('user_id', Auth::user()->id)->where('ticket_status', $request->type)->with(['event', 'ticket', 'user'])->get();
        }
        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No booking found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $bookings
        ], 200);
    }


    public function getETicket($id)
    {
        $e_ticket = TicketSale::where('user_id', Auth::user()->id)->where('link_up_event_id', $id)->with(['event', 'ticket', 'user'])->get();

        if ($e_ticket->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No booking found'
            ], 404);
        }

        $conversations = $e_ticket->map(function ($item) {
            return [
                'event_name' => $item->event->title,
                'date_time' => $item->event->start_time,
                'location' => [
                    'latitude' => $item->event->latitude,
                    'longtitude' => $item->event->longtitude,
                ],
                'user_name' => $item->user->name,
                'user_phone' => $item->user->phone_number,
                'user_email' => $item->user->email,
                'seats' => $item->no_of_tickets,
                'sub_total' => $item->sub_total,
                'fee' => $item->fee,
                'tax' => $item->tax,
                'grand_total' => $item->total,
                'ticket_order_number' => $item->ticket_qrcode_id,
                'coupon_discount' => $item->coupan_amount,
                'from' => $item->pay_type,
                'payment_method' => $item->payment_method,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $conversations
        ], 200);
    }

    public function updateBookingStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'ticket_status' => 'required|string|in:all,pending,canceled,confirmed'
        ]);

        $e_ticket = TicketSale::where('user_id', Auth::id())
            ->where('id', $request->id)
            ->first();

        if (!$e_ticket) {
            return response()->json([
                'status' => false,
                'message' => 'No ticket found'
            ], 404);
        }

        $e_ticket->update([
            'ticket_status' => $request->ticket_status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Updated successfully'
        ], 200);
    }


    // for getting a specific event tickets
    public function getSpecificEventTickets($id)
    {
        $tax = null;
        $event = LinkUpEvent::where('id', $id)->where('status', 'live')->first();
        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }
        $tickets = $event->tickets()
            ->availableForSale()
            ->with(['extraSetting', 'drinkPackage'])
            ->get();

        $tickets->each(function ($ticket) {
            $ticket->append(['cookout', 'wellness']);
        });
        if (!empty($this->country)) {
            $tax = Tax::where('country', $this->country)->first();
        }
        return response()->json([
            'status' => true,
            'tickets' => $tickets,
            'coupons' => $event->coupons()->active()->get(),
            'fee' => floatval(Settings::where('key', 'eventFee')->first()?->value),
            'tax' => $tax,
        ], 200);
    }

    // for creating event ticket checkout
    public function successfullyTicket(Request $request)
    {
        $request->validate([
            'stripe_id' => 'required',
            'stripe_status' => 'required',
            'cart' => 'required|array',
        ]);

        $cart = $request->cart;

        if (! $cart) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid card',
            ], 404);
        }

        $ticketSaleIds = [];
        $totalTickets = 0;
        $amountPaid = 0;

        foreach ($cart['items'] as $item) {
            $ticket = Ticket::findOrFail($item['ticketId']);
            $quantity = $item['qty'];

            // Update ticket inventory
            $ticket->qty_available -= $quantity;
            $ticket->qty_sold += $quantity;
            $ticket->save();
            $netPrice = $item['price'] + $item['fee'] + $item['tax'];
            // Save to ticket sales
            $ticketSale = TicketSale::create([
                'ticket_id' => $ticket->id,
                'ticket_name' => $ticket->name,
                'ticket_qrcode' => $ticket->qrcode,
                'user_id' => auth()->user()->id,
                'link_up_event_id' => $ticket->link_up_event_id,
                'ticket_status' => 'confirmed',
                'payment_method' => 'stripe',
                'pay_type' => 'online',
                'ticket_type' => $ticket->ticket_type,
                'fee' => $item['fee'],
                'discount' => 0.00,
                'tax' => $item['fee'],
                'coupan_amount' => 0.00,
                'sub_total' => $netPrice,
                'total' => $netPrice,
                'no_of_tickets' => $quantity,
                'stripe_id' => $request->stripe_id,
                'stripe_price' => $netPrice,
                'stripe_status' => $request->stripe_status,
            ]);

            $ticketSaleIds[] = $ticketSale->id;
            $totalTickets += (int) $quantity;
            $amountPaid += (float) $netPrice;
        }

        $eventId = null;
        if (!empty($cart['items'][0]['ticketId'])) {
            $t0 = Ticket::find($cart['items'][0]['ticketId']);
            $eventId = $t0?->link_up_event_id;
        }
        $event = $eventId ? LinkUpEvent::find($eventId) : null;
        $buyer = auth()->user();

        // Bump popularity score for event participation
        app(PopularityScoreService::class)->bump($buyer, 4);

        $organizerUserId = null;
        if ($event?->organizer_id && User::whereKey($event->organizer_id)->exists()) {
            $organizerUserId = $event->organizer_id;
        }

        if (!$organizerUserId && $event?->organizer_id) {
            $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
        }

        if (!$organizerUserId && $event?->user_id && User::whereKey($event->user_id)->exists()) {
            $organizerUserId = $event->user_id;
        }

        if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
            $organizerUserId = null;
        }

        if ($event && $organizerUserId) {
            Notification::create([
                'title' => 'New ticket purchase',
                'message' => ($buyer?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
                'send_by' => (string) (auth()->id() ?? ''),
                'user_id' => $organizerUserId,
                'type' => 'payment',
                'context' => 'ticket_purchase',
                'unread' => true,
                'avatar' => $buyer?->avatar ?? null,
                'metadata' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'ticket_sale_ids' => $ticketSaleIds,
                    'tickets_count' => $totalTickets,
                    'amount_paid' => $amountPaid,
                    'payment_method' => 'stripe',
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Ticket(s) booked successfully',
        ]);
    }

    // for creating event ticket checkout
    public function ticketIntant(TicketCardRequest $request)
    {
        $cart = $request->cart;

        $fee = floatval(Settings::where('key', 'eventFee')->first()?->value);
        $tax = 0;

        $ticketIds = array_map(fn($item) => $item['ticketId'], $cart['items']);
        $tickets = Ticket::whereIn('id', $ticketIds)->get();

        $total = 0;

        foreach ($cart['items'] as &$item) {
            $item['ticket'] = $tickets->first(fn($ticket) => $ticket->id === $item['ticketId']);

            if ($item['qty'] < $item['ticket']->min_qty_per_order) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} minimum quantity per order is {$item['ticket']->min_qty_per_order}.",
                ], 400);
            }

            if ($item['qty'] > $item['ticket']->max_qty_per_order) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} maximum quantity per order is {$item['ticket']->max_qty_per_order}.",
                ], 400);
            }

            if ($item['qty'] > $item['ticket']->qty_available) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} has only {$item['ticket']->qty_available} tickets available.",
                ], 400);
            }

            $item['price'] = ($item['ticket']->price ?? 0);
            $item['fee'] = floatval($fee);
            $item['tax'] = floatval($item['tax']);

            // Per-unit total
            $netPerItem = $item['price'] + $item['fee'] + $item['tax'];

            // Total for this ticket line
            $lineTotal = $netPerItem * $item['qty'];

            $total += $lineTotal;
        }

        if ($total === 0) {
            $ticketSaleIds = [];
            $totalTickets = 0;
            foreach ($cart['items'] as $item) {
                $ticketSale = TicketSale::create([
                    'ticket_id' => $item['ticket']->id,
                    'ticket_type' => $item['ticket']->type,
                    'ticket_name' => $item['ticket']->name,
                    'ticket_qrcode' => $item['ticket']->ticket_qrcode,
                    'no_of_tickets' => $item['qty'],
                    'user_id' => auth()->id(),
                    'link_up_event_id' => $item['ticket']->link_up_event_id,
                    'ticket_status' => 'confirmed',
                    'payment_method' => 'cash',
                    'pay_type' => 'Wallet',
                    'fee' => $fee,
                    'discount' => 0.00,
                    'tax' => $tax,
                    'coupan_amount' => 0.00,
                    'sub_total' => $total,
                    'total' => $total,
                ]);

                $ticketSaleIds[] = $ticketSale->id;
                $totalTickets += (int) ($item['qty'] ?? 0);
            }

            $eventId = $cart['items'][0]['ticket']->link_up_event_id ?? null;
            $event = $eventId ? LinkUpEvent::find($eventId) : null;
            $buyer = auth()->user();

            $organizerUserId = null;
            if ($event?->organizer_id && User::whereKey($event->organizer_id)->exists()) {
                $organizerUserId = $event->organizer_id;
            }

            if (!$organizerUserId && $event?->organizer_id) {
                $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
            }

            if (!$organizerUserId && $event?->user_id && User::whereKey($event->user_id)->exists()) {
                $organizerUserId = $event->user_id;
            }

            if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
                $organizerUserId = null;
            }

            if ($event && $organizerUserId) {
                Notification::create([
                    'title' => 'New ticket purchase',
                    'message' => ($buyer?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
                    'send_by' => (string) (auth()->id() ?? ''),
                    'user_id' => $organizerUserId,
                    'type' => 'payment',
                    'context' => 'ticket_purchase',
                    'unread' => true,
                    'avatar' => $buyer?->avatar ?? null,
                    'metadata' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'ticket_sale_ids' => $ticketSaleIds,
                        'tickets_count' => $totalTickets,
                        'amount_paid' => 0,
                        'payment_method' => 'free',
                    ],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Ticket(s) booked successfully',
            ], 200);
        }

        if (! empty($cart['appliedCoupons']) && is_array($cart['appliedCoupons'])) {
            foreach ($cart['appliedCoupons'] as $coupon) {
                if ($coupon['discountType'] === 'percentage') {
                    $discount = ($coupon['discount'] / 100) * $total;
                    $total -= $discount;
                } else {
                    $total -= $coupon['discount'];
                }
            }
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }
        // payment intent creattion
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $amount = (int) $total * 100;

        // Create Stripe customer if not exists
        if (!$user->stripe_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'address' => [
                    'city' => $user->new_city,
                    'state' => $user->new_state,
                    'country' => $user->country,
                ]
            ]);
            $user->stripe_id = $customer->id;
            $user->save();
        }

        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' =>  $user->stripe_id,
        ], [
            'stripe_version' => '2025-05-28.basil',
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' =>  $user->stripe_id,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return response()->json([
            'status' => true,
            'customer_id' =>  $user->stripe_id,
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'ephemeralKey' => $ephemeralKey->secret,
            'created_at' => $paymentIntent->created,
            'publishableKey' => config('services.stripe.key'),
        ], 200);
    }

    // pay payment ticket with wallet
    public function payWithWallet(TicketCardRequest $request)
    {
        $cart = $request->cart;

        $fee = floatval(Settings::where('key', 'eventFee')->first()?->value);
        $tax = 0;

        $ticketIds = array_map(fn($item) => $item['ticketId'], $cart['items']);
        $tickets = Ticket::whereIn('id', $ticketIds)->get();

        $total = 0;

        foreach ($cart['items'] as &$item) {
            $item['ticket'] = $tickets->first(fn($ticket) => $ticket->id === $item['ticketId']);

            if ($item['qty'] < $item['ticket']->min_qty_per_order) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} minimum quantity per order is {$item['ticket']->min_qty_per_order}.",
                ], 400);
            }

            if ($item['qty'] > $item['ticket']->max_qty_per_order) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} maximum quantity per order is {$item['ticket']->max_qty_per_order}.",
                ], 400);
            }

            if ($item['qty'] > $item['ticket']->qty_available) {
                return response()->json([
                    'status' => false,
                    'message' => "{$item['ticket']->name} has only {$item['ticket']->qty_available} tickets available.",
                ], 400);
            }

            $item['price'] = ($item['ticket']->price ?? 0);
            $item['fee'] = floatval($fee);
            $item['tax'] = floatval($item['tax']);

            // Per-unit total
            $netPerItem = $item['price'] + $item['fee'] + $item['tax'];

            // Total for this ticket line
            $lineTotal = $netPerItem * $item['qty'];

            $total += $lineTotal;
        }

        $walletBalance = auth()->user()->balance('USD')->value->get();

        if ($walletBalance < $total) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have sufficient balance in your wallet',
            ], 400);
        }

        if ($total > 0) {
            if (! empty($cart['appliedCoupons']) && is_array($cart['appliedCoupons'])) {
                foreach ($cart['appliedCoupons'] as $coupon) {
                    if ($coupon['discountType'] === 'percentage') {
                        $discount = ($coupon['discount'] / 100) * $total;
                        $total -= $discount;
                    } else {
                        $total -= $coupon['discount'];
                    }
                }
            }

            try {
                transfer($total, 'USD')->from(auth()->user())->to(custodian('e_money'))->commit();
            } catch (\Exception $e) {
                Log::error("Ewallet transfer error: {$e->getMessage()}");
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction failed',
                ], 400);
            }
        }

        $ticketSaleIds = [];
        $totalTickets = 0;
        foreach ($cart['items'] as $item) {
            $ticketSale = TicketSale::create([
                'ticket_id' => $item['ticket']->id,
                'ticket_type' => $item['ticket']->type,
                'ticket_name' => $item['ticket']->name,
                'ticket_qrcode' => $item['ticket']->ticket_qrcode,
                'no_of_tickets' => $item['qty'],
                'user_id' => auth()->id(),
                'link_up_event_id' => $item['ticket']->link_up_event_id,
                'ticket_status' => 'confirmed',
                'payment_method' => 'cash',
                'pay_type' => 'Wallet',
                'fee' => $item['fee'],
                'discount' => 0.00,
                'tax' => $item['tax'],
                'coupan_amount' => 0.00,
                'sub_total' => $total,
                'total' => $total,
            ]);

            $ticketSaleIds[] = $ticketSale->id;
            $totalTickets += (int) ($item['qty'] ?? 0);
        }

        $eventId = $cart['items'][0]['ticket']->link_up_event_id ?? null;
        $event = $eventId ? LinkUpEvent::find($eventId) : null;
        $buyer = auth()->user();

        $organizerUserId = null;
        if ($event?->organizer_id && User::whereKey($event->organizer_id)->exists()) {
            $organizerUserId = $event->organizer_id;
        }

        if (!$organizerUserId && $event?->organizer_id) {
            $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
        }

        if (!$organizerUserId && $event?->user_id && User::whereKey($event->user_id)->exists()) {
            $organizerUserId = $event->user_id;
        }

        if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
            $organizerUserId = null;
        }

        if ($event && $organizerUserId) {
            Notification::create([
                'title' => 'New ticket purchase',
                'message' => ($buyer?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
                'send_by' => (string) (auth()->id() ?? ''),
                'user_id' => $organizerUserId,
                'type' => 'payment',
                'context' => 'ticket_purchase',
                'unread' => true,
                'avatar' => $buyer?->avatar ?? null,
                'metadata' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'ticket_sale_ids' => $ticketSaleIds,
                    'tickets_count' => $totalTickets,
                    'amount_paid' => (float) $total,
                    'payment_method' => 'wallet',
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Ticket(s) booked successfully',
        ], 200);
    }
}
