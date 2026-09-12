<?php

namespace App\Http\Controllers\V1;

use App\Actions\BuildTicketCartAction;
use App\Actions\FilterEventsAction;
use App\Actions\FinalizeTicketSaleAction;
use App\Actions\GetEventIndexDataAction;
use App\Actions\ShowEventAction;
use App\DTOs\EventFilterCriteria;
use App\DTOs\EventIndexFilters;
use App\Exceptions\TicketCartValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFilterRequest;
use App\Http\Requests\EventIndexRequest;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\UserResource;
use App\Mail\BuyCookoutTicketEmailMail;
use App\Mail\BuyTicketEmailMail;
use App\Mail\BuyWellnessTicketEmailMail;
use App\Mail\EventInvitationMail;
use App\Models\Coupon;
use App\Models\CancellationRequests;
use App\Models\EventCategory;
use App\Models\EventFeeSetting;
use App\Models\EventReview;
use App\Models\Frontend\FavouriteEvent;
use App\Models\LinkUpEvent;
use App\Models\OrganizerFollower;
use App\Models\Settings;
use App\Models\Sponsor;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\User;
use App\Repositories\SettingsRepository;
use App\Services\TaxRateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\Stripe;

class EventController extends Controller
{
    /** Return the authenticated user's active or cancelled event bookings. */
    public function bookings(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:active,confirmed,cancelled,canceled'],
        ]);

        $status = $validated['status'] ?? $request->route('status', 'active');
        $isCancelled = in_array($status, ['cancelled', 'canceled'], true);
        $isConfirmed = $status === 'confirmed';

        $bookings = TicketSale::query()
            ->where('user_id', $request->user()->id)
            ->with(['event.eventDetails', 'ticket', 'checkins'])
            ->when(
                $isCancelled,
                fn ($query) => $query->whereIn('ticket_status', ['cancelled', 'canceled', 'Cancelled', 'Canceled']),
                fn ($query) => $query->when(
                    $isConfirmed,
                    fn ($confirmed) => $confirmed->whereIn('ticket_status', ['confirmed', 'Confirmed']),
                    fn ($active) => $active->whereNotIn('ticket_status', ['cancelled', 'canceled', 'Cancelled', 'Canceled']),
                ),
            )
            ->latest('id')
            ->get();

        $bookingGroups = $bookings
            ->groupBy('link_up_event_id')
            ->map(function ($eventBookings) {
                $firstBooking = $eventBookings->first();
                $event = $firstBooking->event;
                $tickets = $eventBookings
                    ->map(fn (TicketSale $booking) => $this->bookingSummary($booking))
                    ->values();

                return [
                    'event_id' => $firstBooking->link_up_event_id,
                    'img' => $event?->image_url ?: asset('assets/images/default-image.png'),
                    'event' => $event?->title ?? $firstBooking->ticket_name ?? 'Event',
                    'event_desc' => $event?->description ?: 'LinkUp Event',
                    'total' => $tickets->sum('paid'),
                    'ticket_count' => $tickets->sum('quantity'),
                    'tickets' => $tickets,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $bookingGroups,
            'meta' => [
                'status' => $isCancelled ? 'cancelled' : ($isConfirmed ? 'confirmed' : 'active'),
                'total' => $bookingGroups->count(),
                'ticket_sales_total' => $bookings->count(),
            ],
        ]);
    }

    /** Cancel one of the authenticated user's bookings immediately. */
    public function cancelBooking(Request $request, TicketSale $ticketSale)
    {
        if ((int) $ticketSale->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'This booking does not belong to the authenticated user.',
            ], 403);
        }

        $status = strtolower((string) $ticketSale->ticket_status);
        if (! in_array($status, ['cancelled', 'canceled'], true)) {
            $ticketSale->update(['ticket_status' => 'cancelled']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled.',
            'data' => [
                'id' => $ticketSale->id,
                'ticket_status' => $ticketSale->fresh()->ticket_status,
            ],
        ]);
    }

    /**
     * Return the complete E-ticket payload used by the web Ticket Detail modal.
     */
    public function showBooking(Request $request, TicketSale $ticketSale)
    {
        if ((int) $ticketSale->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'This booking does not belong to the authenticated user.',
            ], 403);
        }

        $ticketSale->load([
            'event.eventDetails',
            'event.organizer',
            'ticket',
            'checkins',
            'cancellationRequest',
        ]);

        $ticket = array_merge($this->bookingSummary($ticketSale), [
            'sub_total' => (float) ($ticketSale->sub_total ?? 0),
            'total' => (float) ($ticketSale->total ?? $ticketSale->stripe_price ?? 0),
            'fee' => (float) ($ticketSale->fee ?? 0),
            'tax' => (float) ($ticketSale->tax ?? 0),
            'event_tax' => (float) ($ticketSale->event_tax ?? 0),
            'discount' => (float) ($ticketSale->discount ?? 0),
            'coupan_amount' => (float) ($ticketSale->coupan_amount ?? 0),
            'tables_total' => (float) ($ticketSale->tables_total ?? 0),
            'drinks_total' => (float) ($ticketSale->drinks_total ?? 0),
            'wellness_total' => (float) ($ticketSale->wellness_total ?? 0),
            'cookout_total' => (float) ($ticketSale->cookout_total ?? 0),
            'drink_addons' => $ticketSale->drink_addons ?: [],
            'table_addons' => $ticketSale->table_addons ?: [],
            'wellness_addons' => $ticketSale->wellness_addons ?: [],
            'cookout_included_protein' => $ticketSale->cookout_included_protein,
            'cookout_included_sides' => $ticketSale->cookout_included_sides ?: [],
            'cookout_addons' => $ticketSale->cookout_addons ?: [],
            'package_data' => $ticketSale->package_data,
            'fee_breakdown' => $ticketSale->fee_breakdown ?: [],
            'cancellation_request' => $ticketSale->cancellationRequest ? [
                'id' => $ticketSale->cancellationRequest->id,
                'status' => $ticketSale->cancellationRequest->status,
                'refund_amount' => (float) $ticketSale->cancellationRequest->refund_amount,
            ] : null,
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone_number' => $request->user()->phone_number,
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    /** Submit a refund/cancellation request for one of the user's bookings. */
    public function cancelBookingRequest(Request $request, TicketSale $ticketSale)
    {
        if ((int) $ticketSale->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'This booking does not belong to the authenticated user.',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $ticketSale->update(['ticket_status' => 'cancelled']);

        $cancellationRequest = CancellationRequests::create([
            'user_id' => $request->user()->id,
            'ticket_id' => $ticketSale->id,
            'event_id' => $ticketSale->link_up_event_id,
            'admin_reason' => $validated['reason'] ?? null,
            'status' => 'Pending',
            'refund_amount' => $validated['amount'],
            'deduct_ammount' => (float) ($ticketSale->total ?? $ticketSale->stripe_price ?? 0) - (float) $validated['amount'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cancellation request submitted successfully.',
            'data' => [
                'id' => $cancellationRequest->id,
                'ticket_id' => $cancellationRequest->ticket_id,
                'status' => $cancellationRequest->status,
                'refund_amount' => (float) $cancellationRequest->refund_amount,
            ],
        ], 201);
    }

    /** The fields used by the booking card and E-ticket screens. */
    private function bookingSummary(TicketSale $ticketSale): array
    {
        $event = $ticketSale->event;
        $eventDetails = $event?->eventDetails;
        $eventStart = $event?->start_time ?: $eventDetails?->single_event_date;
        $eventEnd = $event?->end_time ?: $eventDetails?->single_event_date ?: $eventStart;
        $paid = (float) ($ticketSale->stripe_price ?? 0);
        if ($paid <= 0) {
            $paid = (float) ($ticketSale->total ?? 0);
        }

        return [
            'id' => $ticketSale->id,
            'event_id' => $ticketSale->link_up_event_id,
            'qr' => $ticketSale->ticket_qrcode ?: $ticketSale->ticket_qrcode_id ?: $ticketSale->id,
            'ticket_qrcode' => $ticketSale->ticket_qrcode ?: $ticketSale->ticket_qrcode_id ?: $ticketSale->id,
            'date' => $ticketSale->created_at?->format('M d, Y'),
            'quantity' => max(1, (int) ($ticketSale->no_of_tickets ?? 1)),
            'no_of_tickets' => max(1, (int) ($ticketSale->no_of_tickets ?? 1)),
            'paid' => $paid,
            'ticket_status' => $ticketSale->ticket_status,
            'checkin' => $ticketSale->checkins->isNotEmpty(),
            'ticket_name' => $ticketSale->ticket_name ?: $ticketSale->ticket?->name ?: 'Ticket',
            'ticket_type' => $ticketSale->ticket_type ?: $ticketSale->ticket?->ticket_type ?: $ticketSale->ticket?->type ?: 'Ticket',
            'event' => [
                'id' => $event?->id,
                'title' => $event?->title ?? $ticketSale->ticket_name ?? 'Event',
                'venue' => $event?->venue,
                'city' => $event?->city,
                'country' => $event?->country,
                'image_url' => $event?->image_url,
                'description' => $event?->description,
                'start_time' => $eventStart,
                'end_time' => $eventEnd,
                'currency_symbol' => $event?->currency_symbol ?? '$',
            ],
        ];
    }

    // for getting all event categories
    public function getAllEventCategories()
    {
        $category = EventCategory::where('status', true)
            ->select('id', 'name')->get();
        if ($category->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No event categories found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $category
        ], 200);
    }

    // for getting all events by category id
    public function getAllEventsByCategory($id)
    {
        $date = Carbon::now();
        $events = LinkUpEvent::where('category_id', $id)->where('end_time', '>=', $date)->with('user_favourite')->get();

        $events->transform(function ($event) {
            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });

        if ($events->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No events found for this category'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $events
        ], 200);
    }

    // for searching events by name
    public function searchEvents(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
        ]);

        $date = Carbon::now();
        $events = LinkUpEvent::query()
            ->filter($request->only('search'))->where('end_time', '>=', $date)
            ->orderBy('created_at', 'DESC')
            ->with('user_favourite')
            ->get();


        $events->transform(function ($event) {
            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });


        if ($events->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No events found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $events
        ], 200);
    }

    // for getting all events
    public function getAllEvents()
    {
        $date = Carbon::now();
        $events = LinkUpEvent::where('end_time', '>=', $date)->with('user_favourite')->get();

        $events->transform(function ($event) {
            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });

        if ($events->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No events found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $events
        ], 200);
    }

    // for getting a specific event
    public function getSpecificEvent($id)
    {
        $event = LinkUpEvent::where('id', $id)->with(['user_favourite', 'event_audience'])->get();
        $audience = [];
        $event->transform(function ($event) use ($audience) {
            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            foreach ($event->event_audience as $item) {
                if ($item->user) {
                    $audience[] = new UserResource($item->user);
                }
            }
            $event->audience = $audience;
            unset($event->user_favourite);
            unset($event->event_audience);
            return $event;
        });
        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $event
        ], 200);
    }

    // for getting a specific event
    public function getSpecificEventCoupon($id)
    {
        $date = Carbon::today()->format('Y-m-d');
        $event = Coupon::where('link_up_event_id', $id)
            ->where('expiry_date', '>=', $date)
            ->where('status', 'live')
            ->get();

        if ($event->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $event
        ], 200);
    }

    // for getting trending events
    public function getTrendingEvent(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'state' => 'required|string'
        ]);

        $trending = LinkUpEvent::where('country', $request->country)
            ->where('state', $request->state)
            ->where('likes_count', '>', 0)
            ->orderBy('likes_count', 'desc')
            ->with('user_favourite')
            ->get();

        $trending->transform(function ($event) {

            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });

        if ($trending->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No trending event found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $trending
        ], 200);
    }

    // for getting upcoming events
    public function getUpcomingEvent(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'state' => 'required|string'
        ]);

        $date = Carbon::now();
        $upcoming = LinkUpEvent::where('country', $request->country)
            ->where('state', $request->state)
            ->where('end_time', '>=', $date)
            ->orderBy('created_at', 'DESC')
            ->with('user_favourite')
            ->get();

        $upcoming->transform(function ($event) {

            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });

        if ($upcoming->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No upcoming event found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $upcoming
        ], 200);
    }

    // for getting upcoming events
    public function getNearByEvent()
    {
        $user = Auth::user();
        $distanceFilter = $user->distance_filter ?? ["0.0", "1000.0"];
        $date = Carbon::now();
        $upcoming = LinkUpEvent::nearby($user->latitude, $user->longitude, (float) $distanceFilter[0], (float) $distanceFilter[1])
            ->whereDate('end_time', '>=', $date)
            ->orderBy('created_at', 'DESC')
            ->with('user_favourite')
            ->get();

        $upcoming->transform(function ($event) {

            $toggle = $event->user_favourite !== null;
            $event->is_favourite = ($toggle == 'true' ? 1 : 0);
            unset($event->user_favourite);
            return $event;
        });

        if ($upcoming->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No near by event found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $upcoming
        ], 200);
    }

    // mark as favourite / un-favourite
    public function markFavouriteEvent(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer'
        ]);

        $user = Auth::user();
        $fav_event = FavouriteEvent::where('event_id', $request->event_id)
            ->where('user_id', $user->id)
            ->first();

        $event = LinkUpEvent::find($request->event_id);
        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found'
            ], 404);
        }

        if (is_null($fav_event)) {

            FavouriteEvent::create([
                'event_id' => $request->event_id,
                'user_id' => $user->id
            ]);

            $event->likes_count = (int) $event->likes_count + 1;
            $event->save();

            return response()->json([
                'status' => true,
                'message' => 'Event added to favorites'
            ], 200);
        } else {
            // Remove from favorites
            FavouriteEvent::where('event_id', $request->event_id)
                ->where('user_id', $user->id)
                ->delete();

            $event->likes_count = max(0, (int) $event->likes_count - 1);
            $event->save();

            return response()->json([
                'status' => true,
                'message' => 'Event removed from favorites'
            ], 200);
        }
    }

    public function getFavouriteEvent()
    {
        $user = Auth::user();
        $events = FavouriteEvent::where('user_id', $user->id)->with(['event'])->get();

        if ($events->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No favourite event found'
            ], 404);
        }

        $favEvents = $events->map(function ($fav) {
            return $fav->event;
        })->filter()->values();

        return response()->json([
            'status' => true,
            'data' => EventListResource::collection($favEvents),
        ], 200);
    }

    // event home feed: filtered events + category rails, mirrors Frontend\EventController::index
    public function homeData(EventIndexRequest $request, GetEventIndexDataAction $action)
    {
        $filters = EventIndexFilters::fromRequest($request);
        $data = $action->handle(Auth::user(), $filters);

        return response()->json([
            'status' => true,
            'data' => [
                'events' => EventListResource::collection($data['events']),
                'new_providence' => EventListResource::collection($data['newProvidence']),
                'event_low_cost' => EventListResource::collection($data['eventLowCost']),
                'jamaica_event' => EventListResource::collection($data['jamaicaEvent']),
                'free_events' => EventListResource::collection($data['freeEvents']),
            ],
        ], 200);
    }

    // mirrors Frontend\EventController::filterEvents / searchEvents (status+search+country filtering)
    public function filterEvents(EventFilterRequest $request, FilterEventsAction $action)
    {
        $criteria = EventFilterCriteria::fromRequest($request);
        $data = $action->handle($criteria);

        return response()->json([
            'status' => true,
            'data' => [
                'events' => EventListResource::collection($data['events']),
            ],
        ], 200);
    }

    // mirrors Frontend\EventController::show
    public function showEvent(LinkUpEvent $event, ShowEventAction $action)
    {
        $data = $action->handle($event);

        return response()->json([
            'status' => true,
            'data' => [
                'event' => new EventResource($data['event']),
                'tickets' => TicketResource::collection($data['tickets']),
                'other_events' => EventListResource::collection($data['otherEvents']),
                'sponsor' => $data['sponsor'],
                'organizer_stats' => $data['organizerStats'],
                'attendees' => $data['attendees'],
                'show_map' => $data['show_map'],
                'event_fee_settings' => $data['eventFeeSettings'],
                'currency' => $data['currency'],
                'is_wellness_event' => $data['isWellnessEvent'],
                'is_cookout_event' => $data['isCookoutEvent'],
            ],
        ], 200);
    }

    // mirrors Frontend\EventController::eventTickets
    public function eventTickets(LinkUpEvent $event, SettingsRepository $settings)
    {
        $event->loadMissing(['category', 'authUserFavorite']);

        $tickets = $event->tickets()->with(['extraSetting', 'wellnessSlotBlocks'])->get();
        $otherEvents = LinkUpEvent::where('organizer_id', $event->organizer_id)
            ->where('id', '!=', $event->id)
            ->take(8)
            ->get();

        $taxService = new TaxRateService();
        $taxRules = $taxService->getStateTaxRules(
            strtoupper($event->state ?? ''),
            $event->zip ?? null,
            $event->city ?? null,
            $event->country ?? 'US'
        );

        $taxRate = $taxRules['success'] ? ($taxRules['rate'] * 100) : 0;
        $taxConfig = [
            'rate' => $taxRate,
            'tax_fees' => $taxRules['tax_fees'] ?? false,
            'no_state_sales_tax' => $taxRules['no_state_sales_tax'] ?? false,
        ];

        return response()->json([
            'status' => true,
            'data' => [
                'tickets' => TicketResource::collection($tickets),
                'coupons' => $event->coupons()->active()->get(),
                'fee' => floatval(Settings::where('key', 'eventFee')->first()?->value),
                'tax' => $taxRate,
                'tax_config' => $taxConfig,
                // the actual fee breakdown Show.vue's order summary is built
                // from (service/processing/vip/mobile pct+fixed fees) —
                // 'fee'/'tax_config' above are only the flat values the
                // standalone Tickets.vue page uses.
                'event_fee_settings' => $settings->getFeeSettingsForEvent($event),
            ],
        ], 200);
    }

    // Buys ticket(s) with the buyer's in-app wallet balance. Mirrors
    // Frontend\WalletController::buyTicket, but that method returns
    // back()->withErrors()/Inertia::render(), both of which need a session —
    // this route runs under auth:sanctum with no session, so pricing
    // (BuildTicketCartAction) and persistence (FinalizeTicketSaleAction) were
    // pulled into shared actions and this method just adapts them to JSON.
    public function buyTicketWallet(Request $request, LinkUpEvent $event, BuildTicketCartAction $buildCart, FinalizeTicketSaleAction $finalize)
    {
        $validated = $request->validate($this->ticketCartValidationRules());

        try {
            $cart = $buildCart->handle($validated);
        } catch (TicketCartValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors], 422);
        }

        if ($cart['event']->id !== $event->id) {
            return response()->json(['status' => false, 'message' => 'Cart tickets do not belong to this event'], 422);
        }

        $user = Auth::user();
        $walletBalance = $user->balance('USD')->value->get();
        if ($walletBalance < $cart['total']) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have sufficient balance in your wallet',
            ], 422);
        }

        if ($cart['total'] > 0) {
            try {
                transfer($cart['total'], 'USD')->from($user)->to(custodian('e_money'))->commit();
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Transaction failed'], 422);
            }
        }

        $orderId = 'WALLET-' . (string) Str::uuid();
        $result = DB::transaction(fn () => $finalize->handle($cart, $user->id, $orderId, 'cash', 'Wallet'));

        $this->sendTicketPurchaseEmails($result['ticket_sales'], $result['event'], $user);

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $orderId,
                'fees_summary' => $result['fees_summary'],
                'ticket_sales' => $result['ticket_sales'],
            ],
        ], 200);
    }

    // Initiates a card purchase via Stripe Checkout. Mirrors
    // Frontend\StripeController::buyTicket, which stashes the priced cart in
    // the PHP session between creating the Checkout Session and the
    // payment-success redirect. A token-authenticated client can't rely on
    // that session surviving a round trip to Stripe's hosted page and back,
    // so the priced cart is cached under a random token instead, and that
    // token rides along in the success_url for buyTicketStripeSuccess() to
    // recover — see that method for how the callback is trusted without auth.
    public function buyTicketStripeInitiate(Request $request, LinkUpEvent $event, BuildTicketCartAction $buildCart)
    {
        $validated = $request->validate($this->ticketCartValidationRules());

        try {
            $cart = $buildCart->handle($validated);
        } catch (TicketCartValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors], 422);
        }

        if ($cart['event']->id !== $event->id) {
            return response()->json(['status' => false, 'message' => 'Cart tickets do not belong to this event'], 422);
        }

        $stripeKey = config('services.stripe.secret');
        if (!$stripeKey) {
            return response()->json(['status' => false, 'message' => 'Payment configuration error'], 500);
        }

        $lineItems = $this->buildStripeLineItems($cart);
        if (empty($lineItems) && $cart['total'] > 0) {
            return response()->json(['status' => false, 'message' => 'No valid items found for payment. Please check your cart.'], 422);
        }

        Stripe::setApiKey($stripeKey);
        $cartToken = (string) Str::uuid();

        try {
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('api.stripe.ticket.buy.success') . '?checkout_session_id={CHECKOUT_SESSION_ID}&cart_token=' . $cartToken,
                'cancel_url' => config('app.url'),
                'client_reference_id' => $cartToken,
                'metadata' => [
                    'user_id' => Auth::id(),
                    'event_id' => $cart['event']->id,
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['status' => false, 'message' => 'Failed to initiate payment: ' . $e->getMessage()], 422);
        }

        Cache::put(
            "api_ticket_cart:{$cartToken}",
            [
                'cart' => $this->serializeCartForCache($cart),
                'user_id' => Auth::id(),
                'checkout_session_id' => $checkoutSession->id,
            ],
            now()->addHours(2)
        );

        return response()->json([
            'status' => true,
            'data' => [
                'checkout_url' => $checkoutSession->url,
                'cart_token' => $cartToken,
            ],
        ], 200);
    }

    // Public callback Stripe redirects the browser to after Checkout. A
    // redirect carries no Authorization header, so this route is registered
    // outside auth:sanctum (see routes/api.php). Trust comes from requiring
    // both the opaque cart_token minted in buyTicketStripeInitiate() (known
    // only to the browser that was redirected here) and Stripe confirming,
    // server-to-server, that the session it names was actually paid.
    public function buyTicketStripeSuccess(Request $request, FinalizeTicketSaleAction $finalize)
    {
        $token = $request->query('cart_token');
        $sessionId = $request->query('checkout_session_id');

        if (!$token || !$sessionId) {
            return response()->json(['status' => false, 'message' => 'Missing checkout reference'], 422);
        }

        $cacheKey = "api_ticket_cart:{$token}";
        $cached = Cache::get($cacheKey);
        if (!$cached) {
            return response()->json(['status' => false, 'message' => 'This checkout has expired or was already processed'], 410);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $checkoutSession = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['status' => false, 'message' => 'Payment verification failed'], 422);
        }

        if ($checkoutSession->id !== $cached['checkout_session_id'] || $checkoutSession->client_reference_id !== $token) {
            return response()->json(['status' => false, 'message' => 'Checkout reference mismatch'], 422);
        }

        if ($checkoutSession->payment_status !== 'paid') {
            return response()->json(['status' => false, 'message' => 'Payment not completed'], 422);
        }

        // Consume the token immediately so a reloaded/replayed success URL
        // can't finalize the same paid session twice.
        Cache::forget($cacheKey);

        $cart = $this->deserializeCartFromCache($cached['cart']);
        $result = DB::transaction(fn () => $finalize->handle($cart, $cached['user_id'], $sessionId, 'card', 'Stripe'));

        $user = User::find($cached['user_id']);
        $this->sendTicketPurchaseEmails($result['ticket_sales'], $result['event'], $user);

        return response()->json([
            'status' => true,
            'data' => [
                'fees_summary' => $result['fees_summary'],
                'ticket_sales' => $result['ticket_sales'],
            ],
        ], 200);
    }

    private function ticketCartValidationRules(): array
    {
        return [
            'items' => 'array',
            'items.*.ticketId' => 'integer',
            'items.*.qty' => 'integer|min:0',
            'items.*.addons' => 'array|nullable',
            'items.*.addons.*.addon_id' => 'sometimes|integer|nullable',
            'items.*.addons.*.name' => 'sometimes|string|nullable',
            'items.*.addons.*.quantity' => 'integer',
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
            'items.*.wellness.serviceMode' => 'sometimes|string|in:mobile,inhouse|nullable',
            'items.*.wellness.selectedSlot' => 'sometimes|string|nullable',
            'items.*.wellness.selectedSlotDate' => 'sometimes|string|nullable',
            'items.*.wellness.holdExpiresAt' => 'sometimes|numeric|nullable',
            'items.*.wellness.includedService' => 'sometimes|string|nullable',
            'items.*.wellness.services' => 'sometimes|array|nullable',
            'items.*.wellness.services.*.name' => 'required_with:items.*.wellness.services|string',
            'items.*.wellness.services.*.qty' => 'required_with:items.*.wellness.services|integer|min:0',
            'items.*.wellness.manualAddons' => 'sometimes|array|nullable',
            'items.*.wellness.manualAddons.*.name' => 'required_with:items.*.wellness.manualAddons|string',
            'items.*.wellness.manualAddons.*.qty' => 'required_with:items.*.wellness.manualAddons|integer|min:0',
            'items.*.wellness.contactPhone' => 'sometimes|string|nullable',
            'appliedCoupons' => 'array',
            'appliedCoupons.*' => 'string',
        ];
    }

    private function buildStripeLineItems(array $cart): array
    {
        $currency = $cart['currency'];
        $lineItems = [];
        $ticketLineIndexes = [];

        foreach ($cart['items'] as $item) {
            if (($item['qty'] ?? 0) <= 0 && ($item['addon_total'] ?? 0) <= 0) {
                continue;
            }

            $ticket = $item['ticket'];

            if ($item['qty'] > 0) {
                $ticketName = $ticket->name;
                $wellnessConfig = $ticket->wellness;
                if (is_array($wellnessConfig) && ($wellnessConfig['includeService'] ?? null) === 'yes') {
                    $ticketName .= ' (Wellness)';
                    $selectedMode = $item['wellness']['serviceMode'] ?? null;
                    if ($selectedMode) {
                        $ticketName .= ' • ' . ($selectedMode === 'mobile' ? 'Mobile' : 'In-house');
                    }
                }

                $lineItems[] = [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => ['name' => $ticketName],
                        'unit_amount' => (int) round($item['price'] * 100, 0),
                    ],
                    'quantity' => $item['qty'],
                ];
                $ticketLineIndexes[] = count($lineItems) - 1;

                foreach ($item['cookout_addons_json'] ?? [] as $addon) {
                    if (($addon['quantity'] ?? 0) > 0) {
                        $lineItems[] = $this->addonLineItem($currency, $addon);
                    }
                }
                foreach ($item['wellness_addons_json'] ?? [] as $addon) {
                    if (($addon['quantity'] ?? 0) > 0) {
                        $lineItems[] = $this->addonLineItem($currency, $addon);
                    }
                }
                foreach ($item['drink_addons_json'] ?? [] as $addon) {
                    if (($addon['quantity'] ?? 0) > 0) {
                        $lineItems[] = $this->addonLineItem($currency, $addon);
                    }
                }
                foreach ($item['table_addons_json'] ?? [] as $addon) {
                    if (($addon['total_price'] ?? 0) > 0 && ($addon['quantity'] ?? 0) > 0) {
                        $unitCents = (int) round((floatval($addon['total_price']) / intval($addon['quantity'])) * 100, 0);
                        $lineItems[] = [
                            'price_data' => [
                                'currency' => $currency,
                                'product_data' => ['name' => 'Table for ' . ($addon['capacity'] ?? 0) . ' — ' . ($addon['section'] ?? '-')],
                                'unit_amount' => $unitCents,
                            ],
                            'quantity' => intval($addon['quantity']),
                        ];
                    }
                }
            }

            $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && $ticket->has_table === 'yes';
            if ($hasPackage) {
                $lineItems[] = $this->packageLineItem($currency, $ticket->drinkPackage);
            }
        }

        $fees = $cart['fees'];
        $feeLines = [
            'Tax' => $fees['tax'],
            'Service Fee (%)' => $fees['service_fee_percent'],
            'Service Fee (Fixed)' => $fees['service_fee_fixed'],
            'Processing Fee (%)' => $fees['processing_fee_percent'],
            'Processing Fee (Fixed)' => $fees['processing_fee_fixed'],
            'Drink Fees' => $fees['drink_fees'],
            'Bottles Fees' => $fees['bottle_fees'],
            'VIP Package Fees (%)' => $fees['vip_fees'],
            'Mobile Fee' => $fees['mobile_fee'],
        ];
        foreach ($feeLines as $label => $amount) {
            if ($amount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => ['name' => $label],
                        'unit_amount' => (int) round($amount * 100, 0),
                    ],
                    'quantity' => 1,
                ];
            }
        }

        $this->applyCouponToLineItems($lineItems, $ticketLineIndexes, $cart['coupon_amount']);

        return $lineItems;
    }

    private function addonLineItem(string $currency, array $addon): array
    {
        return [
            'price_data' => [
                'currency' => $currency,
                'product_data' => ['name' => $addon['name'] . ' (' . Str::title(str_replace('_', ' ', $addon['category'])) . ')'],
                'unit_amount' => (int) round(floatval($addon['unit_price'] ?? 0) * 100, 0),
            ],
            'quantity' => intval($addon['quantity'] ?? 0),
        ];
    }

    private function packageLineItem(string $currency, $package): array
    {
        $description = [];
        if (!empty($package->bottles) && is_array($package->bottles)) {
            $description[] = '🍹 Bottles: ' . implode(', ', array_map(fn ($b) => $b['qty'] . '× ' . $b['name'], $package->bottles));
        }
        if (!empty($package->chasers) && is_array($package->chasers)) {
            $description[] = '🥤 Chasers: ' . implode(', ', array_map(fn ($c) => $c['qty'] . '× ' . $c['name'], $package->chasers));
        }
        if (!empty($package->waters) && is_array($package->waters)) {
            $description[] = '💧 Waters: ' . implode(', ', array_map(fn ($w) => $w['qty'] . '× ' . $w['name'], $package->waters));
        }

        return [
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => '📦 ' . $package->name,
                    'description' => $description ? implode(' | ', $description) : 'Bottle service package',
                ],
                'unit_amount' => 0,
            ],
            'quantity' => 1,
        ];
    }

    private function applyCouponToLineItems(array &$lineItems, array $ticketLineIndexes, float $couponAmount): void
    {
        if ($couponAmount <= 0 || empty($lineItems) || empty($ticketLineIndexes)) {
            return;
        }

        $remainingCoupon = (int) round($couponAmount * 100, 0);
        $ticketCentsTotal = 0;
        foreach ($ticketLineIndexes as $idx) {
            $ticketCentsTotal += ((int) $lineItems[$idx]['price_data']['unit_amount']) * ((int) ($lineItems[$idx]['quantity'] ?? 1));
        }
        if ($remainingCoupon > $ticketCentsTotal) {
            $remainingCoupon = $ticketCentsTotal;
        }
        if ($remainingCoupon <= 0) {
            return;
        }

        foreach ($ticketLineIndexes as $idx) {
            if ($remainingCoupon <= 0) {
                break;
            }
            $unit = (int) $lineItems[$idx]['price_data']['unit_amount'];
            $qty = (int) ($lineItems[$idx]['quantity'] ?? 1);
            $lineTotal = $unit * $qty;
            if ($lineTotal <= 0) {
                continue;
            }
            if ($remainingCoupon >= $lineTotal) {
                $lineItems[$idx]['price_data']['unit_amount'] = 0;
                $remainingCoupon -= $lineTotal;
            } else {
                $newLineTotal = $lineTotal - $remainingCoupon;
                $newUnit = (int) floor($newLineTotal / max(1, $qty));
                $lineItems[$idx]['price_data']['unit_amount'] = max(0, $newUnit);
                $remainingCoupon = 0;
            }
        }
    }

    private function serializeCartForCache(array $cart): array
    {
        return [
            'items' => array_map(function ($item) {
                $item['ticket_id'] = $item['ticket']->id;
                unset($item['ticket']);
                return $item;
            }, $cart['items']),
            'event_id' => $cart['event']->id,
            'event_fee_settings_id' => $cart['event_fee_settings']->id ?? null,
            'currency' => $cart['currency'],
            'cart_subtotal' => $cart['cart_subtotal'],
            'fees' => $cart['fees'],
            'coupon_amount' => $cart['coupon_amount'],
            'total' => $cart['total'],
        ];
    }

    private function deserializeCartFromCache(array $data): array
    {
        $ticketIds = array_column($data['items'], 'ticket_id');
        $tickets = Ticket::whereIn('id', $ticketIds)->with(['drinkPackage'])->get()->keyBy('id');

        $items = array_map(function ($item) use ($tickets) {
            $item['ticket'] = $tickets->get($item['ticket_id']);
            return $item;
        }, $data['items']);

        return [
            'items' => $items,
            'event' => LinkUpEvent::find($data['event_id']),
            'event_fee_settings' => $data['event_fee_settings_id'] ? EventFeeSetting::find($data['event_fee_settings_id']) : null,
            'currency' => $data['currency'],
            'cart_subtotal' => $data['cart_subtotal'],
            'fees' => $data['fees'],
            'coupon_amount' => $data['coupon_amount'],
            'total' => $data['total'],
        ];
    }

    private function sendTicketPurchaseEmails($ticketSales, ?LinkUpEvent $event, ?User $user): void
    {
        if (!$event || !$user?->email) {
            return;
        }

        $sponsor = Sponsor::where('link_up_event_id', $event->id)->get();
        $appUrl = config('app.url');

        $wellnessTickets = collect();
        $cookoutTickets = collect();
        $standardTickets = collect();

        foreach ($ticketSales as $ticketSale) {
            $type = strtolower(trim((string) ($ticketSale->ticket_type ?? '')));
            if (str_contains($type, 'wellness') || str_contains($type, 'spa')) {
                $wellnessTickets->push($ticketSale);
            } elseif (str_contains($type, 'cookout')) {
                $cookoutTickets->push($ticketSale);
            } else {
                $standardTickets->push($ticketSale);
            }
        }

        if ($wellnessTickets->isNotEmpty()) {
            Mail::to($user->email)->queue(new BuyWellnessTicketEmailMail($wellnessTickets, $event, $appUrl, $user, $sponsor));
        }
        if ($cookoutTickets->isNotEmpty()) {
            Mail::to($user->email)->queue(new BuyCookoutTicketEmailMail($cookoutTickets, $event, $appUrl, $user, $sponsor));
        }
        if ($standardTickets->isNotEmpty()) {
            Mail::to($user->email)->queue(new BuyTicketEmailMail($standardTickets, $event, $appUrl, $user, $sponsor));
        }
    }

    // mirrors Frontend\EventController::toggleFavorite, but takes {event} from the route
    public function toggleFavoriteByEvent(LinkUpEvent $event)
    {
        $user = Auth::user();

        $favorite = FavouriteEvent::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $favorite) {
            FavouriteEvent::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Added to favorite',
            ], 200);
        }

        $favorite->delete();

        return response()->json([
            'status' => true,
            'message' => 'Removed from favorite',
        ], 200);
    }

    // mirrors Frontend\EventController::toggleFollowOrganizer
    public function toggleFollowOrganizer(Request $request)
    {
        $request->validate([
            'organizer_id' => 'required|integer|exists:organizer_profiles,id',
        ]);

        $user = Auth::user();
        $organizerId = $request->organizer_id;

        $existingFollow = OrganizerFollower::where('user_id', $user->id)
            ->where('organizer_id', $organizerId)
            ->first();

        if ($existingFollow) {
            $existingFollow->delete();

            return response()->json([
                'status' => true,
                'message' => 'Unfollowed organizer successfully',
                'is_following' => false,
            ], 200);
        }

        OrganizerFollower::create([
            'user_id' => $user->id,
            'organizer_id' => $organizerId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Following organizer successfully',
            'is_following' => true,
        ], 200);
    }

    // mirrors Frontend\EventController::fetchUserForInvitation, trimmed to needed columns only
    public function fetchUserForInvitation()
    {
        $users = User::select('id', 'name', 'email', 'avatar')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users),
        ], 200);
    }

    // mirrors Frontend\EventController::sendUserInvitationEmail
    public function sendUserInvitationEmail(Request $request)
    {
        $request->validate([
            'event' => 'required',
            'eventLink' => 'required|string',
            'email' => 'nullable|string',
            'userEmail' => 'nullable|array',
            'userEmail.*' => 'email',
        ]);

        $emails = [];
        if (!empty($request->email)) {
            $emails = array_merge($emails, explode(',', $request->email));
        }
        if (!empty($request->userEmail)) {
            $emails = array_merge($emails, $request->userEmail);
        }

        $emails = array_filter(array_map('trim', $emails));

        foreach ($emails as $email) {
            Mail::to($email)->send(new EventInvitationMail($request->event, $request->eventLink));
        }

        return response()->json([
            'status' => true,
            'message' => 'Invitations sent successfully!',
        ], 200);
    }

    /**
     * Store a new review for an event
     */
    public function reviewStore(Request $request, LinkUpEvent $event)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'reviewer_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already reviewed this event
        if (Auth::check()) {
            $existingReview = EventReview::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this event.'
                ], 409);
            }
        }

        $review = EventReview::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'reviewer_name' => $request->reviewer_name,
            'status' => 'approved' // Auto-approve for now, can be changed to 'pending'
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Review added successfully.',
            'review' => $review
        ]);
    }

    /**
     * Get reviews for an event (top 5 latest)
     */
    public function reviewsIndex(LinkUpEvent $event)
    {
        $userId = Auth::id();
        $isEventOwner = Auth::check() && ($event->organizer_id === $userId || $event->user_id === $userId);

        $reviews = $event->approvedReviews()
            ->orderBy('created_at', 'desc')
            ->take(5) // Limit to top 5 latest reviews
            ->get()
            ->map(function ($review) use ($userId, $isEventOwner) {
                $isReviewAuthor = Auth::check() && $userId === $review->user_id;

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'reviewer_name' => $review->reviewer_name,
                    'user_id' => $review->user_id,
                    'created_at' => $review->created_at->format('M d, Y'),
                    'can_edit' => $isReviewAuthor,
                    'can_delete' => $isEventOwner || $isReviewAuthor
                ];
            });

        $averageRating = $event->average_rating;
        $reviewsCount = $event->reviews_count;

        // Check if current user has already reviewed this event
        $userHasReviewed = false;
        if (Auth::check()) {
            $userHasReviewed = EventReview::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->exists();
        }

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
            'user_has_reviewed' => $userHasReviewed
        ]);
    }

    /**
     * Update an existing review
     */
    public function reviewUpdate(Request $request, LinkUpEvent $event, EventReview $review)
    {
        // Check if the authenticated user owns this review
        if (!Auth::check() || Auth::id() !== $review->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own reviews.'
            ], 403);
        }

        // Check if the review belongs to this event
        if ($review->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this event.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'reviewer_name' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update([
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'reviewer_name' => $request->reviewer_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'review' => $review
        ]);
    }

    /**
     * Delete a review (only event owner or review author can delete)
     */
    public function reviewDestroy(Request $request, LinkUpEvent $event, EventReview $review)
    {
        // Check if the review belongs to this event
        if ($review->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this event.'
            ], 404);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to delete reviews.'
            ], 401);
        }

        $userId = Auth::id();

        // Check if user is the event owner or the review author
        $isEventOwner = $event->organizer_id === $userId || $event->user_id === $userId;
        $isReviewAuthor = $review->user_id === $userId;

        if (!$isEventOwner && !$isReviewAuthor) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews or reviews on your events.'
            ], 403);
        }

        // Delete the review
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.'
        ]);
    }
}
