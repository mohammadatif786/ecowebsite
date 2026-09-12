<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventTicketDrink;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\DrinkPackage;
use App\Models\Ticket;
use App\Models\TicketExtraSetting;
use App\Models\WellnessSlotBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    private function wellnessBlocksByDateForTicket(int $ticketId)
    {
        $blocks = WellnessSlotBlock::query()
            ->where('ticket_id', $ticketId)
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get(['slot_date', 'start_time', 'end_time', 'count']);

        return $blocks->groupBy(fn($b) => $b->slot_date->format('Y-m-d'))->map(function ($list) {
            return $list->map(fn($b) => [
                'start' => substr((string) $b->start_time, 0, 5),
                'end' => substr((string) $b->end_time, 0, 5),
                'count' => (int) $b->count,
            ])->values();
        });
    }

    private function wellnessBlocksByDateForTicketIds(array $ticketIds)
    {
        if (empty($ticketIds)) {
            return collect();
        }

        $blocks = WellnessSlotBlock::query()
            ->whereIn('ticket_id', $ticketIds)
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get(['ticket_id', 'slot_date', 'start_time', 'end_time', 'count']);

        return $blocks
            ->groupBy('ticket_id')
            ->map(function ($list) {
                return $list
                    ->groupBy(fn($b) => $b->slot_date->format('Y-m-d'))
                    ->map(function ($byDate) {
                        return $byDate->map(fn($b) => [
                            'start' => substr((string) $b->start_time, 0, 5),
                            'end' => substr((string) $b->end_time, 0, 5),
                            'count' => (int) $b->count,
                        ])->values();
                    });
            });
    }

    private function hideExtraSettingFromTicket($ticket): void
    {
        $ticket->makeHidden(['extraSetting', 'extra_setting']);
    }

    private function num($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    private function moneyString(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }

    private function wellnessBookingTotalForTicket($ticket): array
    {
        $wellness = $ticket->wellness;
        $include = is_array($wellness) ? ($wellness['includeService'] ?? null) : null;
        if ($include !== 'yes') {
            return [
                'baseTicketPrice' => $this->moneyString($this->num($ticket->price ?? 0)),
                'selectedUpgrades' => $this->moneyString(0),
                'manualAddons' => $this->moneyString(0),
                'mobileFee' => $this->moneyString(0),
                'grandTotal' => $this->moneyString($this->num($ticket->price ?? 0)),
            ];
        }

        $services = is_array($wellness['services'] ?? null) ? $wellness['services'] : [];
        $manualAddons = is_array($wellness['manualAddons'] ?? null) ? $wellness['manualAddons'] : [];
        $booking = is_array($wellness['booking'] ?? null) ? $wellness['booking'] : [];

        $upgradesTotal = 0.0;
        foreach ($services as $s) {
            if (!is_array($s)) {
                continue;
            }
            if (($s['mode'] ?? null) !== 'addon') {
                continue;
            }
            $upgradesTotal += $this->num($s['price'] ?? 0);
        }

        $manualTotal = 0.0;
        foreach ($manualAddons as $a) {
            if (!is_array($a)) {
                continue;
            }
            $qty = (int) ($a['qty'] ?? 0);
            if ($qty < 0) {
                $qty = 0;
            }
            $manualTotal += $this->num($a['price'] ?? 0) * $qty;
        }

        $mobileFee = 0.0;
        if (($booking['mode'] ?? null) === 'mobile') {
            $mobileFee = $this->num($booking['mobileFee'] ?? 0);
        }

        $base = $this->num($ticket->price ?? 0);
        $grand = $base + $upgradesTotal + $manualTotal + $mobileFee;

        return [
            'baseTicketPrice' => $this->moneyString($base),
            'selectedUpgrades' => $this->moneyString($upgradesTotal),
            'manualAddons' => $this->moneyString($manualTotal),
            'mobileFee' => $this->moneyString($mobileFee),
            'grandTotal' => $this->moneyString($grand),
        ];
    }

    private function cookoutCheckoutTotalForTicket($ticket): array
    {
        $cookout = $ticket->cookout;
        $include = is_array($cookout) ? ($cookout['includeFood'] ?? null) : null;

        $base = $this->num($ticket->price ?? 0);
        if ($include !== 'yes') {
            return [
                'baseTicketPrice' => $this->moneyString($base),
                'selectedAddons' => $this->moneyString(0),
                'grandTotal' => $this->moneyString($base),
            ];
        }

        $proteins = is_array($cookout['proteins'] ?? null) ? $cookout['proteins'] : [];
        $manualAddons = is_array($cookout['manualAddons'] ?? null) ? $cookout['manualAddons'] : [];

        $proteinAddonsTotal = 0.0;
        foreach ($proteins as $p) {
            if (!is_array($p)) {
                continue;
            }
            if (($p['mode'] ?? null) !== 'addon') {
                continue;
            }
            $qty = (int) ($p['qty'] ?? 0);
            if ($qty < 0) {
                $qty = 0;
            }
            $proteinAddonsTotal += $this->num($p['price'] ?? 0) * $qty;
        }

        $manualTotal = 0.0;
        foreach ($manualAddons as $a) {
            if (!is_array($a)) {
                continue;
            }
            $qty = (int) ($a['qty'] ?? 0);
            if ($qty < 0) {
                $qty = 0;
            }
            $manualTotal += $this->num($a['price'] ?? 0) * $qty;
        }

        $addons = $proteinAddonsTotal + $manualTotal;
        $grand = $base + $addons;

        return [
            'baseTicketPrice' => $this->moneyString($base),
            'selectedAddons' => $this->moneyString($addons),
            'grandTotal' => $this->moneyString($grand),
        ];
    }

    private function checkoutTotalForTicket($ticket): array
    {
        $wellness = $ticket->wellness;
        $cookout = $ticket->cookout;

        $wellnessEnabled = is_array($wellness) && (($wellness['includeService'] ?? null) === 'yes');
        $cookoutEnabled = is_array($cookout) && (($cookout['includeFood'] ?? null) === 'yes');

        $base = $this->moneyString($this->num($ticket->price ?? 0));

        if ($wellnessEnabled) {
            $w = $this->wellnessBookingTotalForTicket($ticket);
            return [
                'mode' => 'wellness',
                'baseTicketPrice' => $base,
                'selectedAddons' => $this->moneyString(0),
                'selectedUpgrades' => $w['selectedUpgrades'],
                'manualAddons' => $w['manualAddons'],
                'mobileFee' => $w['mobileFee'],
                'grandTotal' => $w['grandTotal'],
            ];
        }

        if ($cookoutEnabled) {
            $c = $this->cookoutCheckoutTotalForTicket($ticket);
            return [
                'mode' => 'cookout',
                'baseTicketPrice' => $base,
                'selectedAddons' => $c['selectedAddons'],
                'selectedUpgrades' => $this->moneyString(0),
                'manualAddons' => $this->moneyString(0),
                'mobileFee' => $this->moneyString(0),
                'grandTotal' => $c['grandTotal'],
            ];
        }

        return [
            'mode' => 'none',
            'baseTicketPrice' => $base,
            'selectedAddons' => $this->moneyString(0),
            'selectedUpgrades' => $this->moneyString(0),
            'manualAddons' => $this->moneyString(0),
            'mobileFee' => $this->moneyString(0),
            'grandTotal' => $base,
        ];
    }

    /**
     * Get ticket creation data (drinks and events)
     */
    public function index()
    {
        $user = Auth::user();

        $mixDrinks = EventTicketDrink::where('type', 'mixDrinks')
            ->where('created_by', $user->id)
            ->get();

        $wines = EventTicketDrink::where('type', 'wines')
            ->where('created_by', $user->id)
            ->get();

        $waters = EventTicketDrink::where('type', 'waters')
            ->where('created_by', $user->id)
            ->get();

        $beers = EventTicketDrink::where('type', 'beers')
            ->where('created_by', $user->id)
            ->get();

        $softDrinks = EventTicketDrink::where('type', 'softDrinks')
            ->where('created_by', $user->id)
            ->get();

        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $events = LinkUpEvent::where('organizer_id', $organizerProfile->id)
            ->select('id', 'title')
            ->get();
        
        $packages = DrinkPackage::where('organizer_id', $organizerProfile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'drinks' => [
                'mix_drinks' => $mixDrinks,
                'wines' => $wines,
                'waters' => $waters,
                'beers' => $beers,
                'soft_drinks' => $softDrinks,
            ],
            'events' => $events,
            'packages' => $packages,
        ]);
    }

    /**
     * Bulk fetch ticket extras
     */
    public function bulkExtras(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->firstOrFail();

        $ticketIds = Ticket::query()
            ->whereIn('id', $request->ticket_ids)
            ->whereHas('event', function ($q) use ($organizer) {
                $q->where('organizer_id', $organizer->id);
            })
            ->pluck('id')
            ->all();

        $extras = TicketExtraSetting::query()
            ->whereIn('ticket_id', $ticketIds)
            ->get()
            ->keyBy('ticket_id')
            ->map(fn($row) => [
                'cookout' => $row->cookout,
                'wellness' => $row->wellness,
            ]);

        return response()->json([
            'extras' => $extras,
        ], 200);
    }

    /**
     * Upsert ticket extras (cookout/wellness)
     */
    public function upsertExtras(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['error' => 'Ticket not found'], 404);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        if (!$organizer) return response()->json(['error' => 'Organizer not found'], 404);

        if ($ticket->event->organizer_id != $organizer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'cookout' => 'nullable|array',
            'wellness' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $extrasData = [];
        if ($request->has('cookout')) {
            $extrasData['cookout'] = $request->cookout;
        }
        if ($request->has('wellness')) {
            $extrasData['wellness'] = $request->wellness;
        }

        $row = TicketExtraSetting::updateOrCreate(
            ['ticket_id' => $ticket->id],
            $extrasData
        );

        return response()->json([
            'message' => 'Ticket extras saved',
            'extra' => [
                'ticket_id' => $row->ticket_id,
                'cookout' => $row->cookout,
                'wellness' => $row->wellness,
            ],
        ], 200);
    }

    /**
     * Get wellness blocks
     */
    public function getWellnessBlocks(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['error' => 'Ticket not found'], 404);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        if (!$organizer || $ticket->event->organizer_id != $organizer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = WellnessSlotBlock::query()->where('ticket_id', $ticket->id);
        if ($request->date) {
            $query->whereDate('slot_date', $request->date);
        }

        $blocks = $query
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get(['slot_date', 'start_time', 'end_time', 'count']);

        if ($request->date) {
            return response()->json([
                'date' => (string) $request->date,
                'blocks' => $blocks->map(fn($b) => [
                    'start' => substr((string) $b->start_time, 0, 5),
                    'end' => substr((string) $b->end_time, 0, 5),
                    'count' => (int) $b->count,
                ])->values(),
            ], 200);
        }

        $grouped = $blocks->groupBy(fn($b) => $b->slot_date->format('Y-m-d'))->map(function ($list) {
            return $list->map(fn($b) => [
                'start' => substr((string) $b->start_time, 0, 5),
                'end' => substr((string) $b->end_time, 0, 5),
                'count' => (int) $b->count,
            ])->values();
        });

        return response()->json([
            'blocksByDate' => $grouped,
        ], 200);
    }

    /**
     * Store wellness block
     */
    public function storeWellnessBlock(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['error' => 'Ticket not found'], 404);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        if (!$organizer || $ticket->event->organizer_id != $organizer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'slot_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'count' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delta = (int) ($request->count ?? 1);

        $row = WellnessSlotBlock::query()->firstOrNew([
            'ticket_id' => $ticket->id,
            'slot_date' => $request->slot_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $row->count = max(1, (int) ($row->count ?? 0) + $delta);
        $row->save();

        return response()->json([
            'message' => 'Block saved',
            'block' => [
                'slot_date' => (string) $row->slot_date->format('Y-m-d'),
                'start' => substr((string) $row->start_time, 0, 5),
                'end' => substr((string) $row->end_time, 0, 5),
                'count' => (int) $row->count,
            ],
        ], 200);
    }

    /**
     * Clear wellness blocks for a date
     */
    public function clearWellnessDate(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['error' => 'Ticket not found'], 404);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        if (!$organizer || $ticket->event->organizer_id != $organizer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'slot_date' => 'required|date',
        ]);
        
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        WellnessSlotBlock::query()
            ->where('ticket_id', $ticket->id)
            ->whereDate('slot_date', $request->slot_date)
            ->delete();

        return response()->json([
            'message' => 'Date blocks cleared',
        ], 200);
    }

    /**
     * Clear all wellness blocks
     */
    public function clearAllWellnessBlocks(Request $request, $ticketId)
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['error' => 'Ticket not found'], 404);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        if (!$organizer || $ticket->event->organizer_id != $organizer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        WellnessSlotBlock::query()
            ->where('ticket_id', $ticket->id)
            ->delete();

        return response()->json([
            'message' => 'All blocks cleared',
        ], 200);
    }

    /**
     * Get all tickets for organizer's events (without pagination)
     */
    public function gettingTickets()
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        
        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $events = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');
        $tickets = Ticket::whereIn('event_id', $events)->with(['event', 'extraSetting'])->get();

        $blocksByTicketId = $this->wellnessBlocksByDateForTicketIds($tickets->pluck('id')->all());
        $tickets->each(function($ticket) use ($blocksByTicketId) {
            $ticket->append(['cookout', 'wellness']);
            $ticket->setAttribute('blocksByDate', $blocksByTicketId->get($ticket->id, (object) []));
            $ticket->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticket));
            $ticket->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticket));
            $ticket->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticket));
            $this->hideExtraSettingFromTicket($ticket);
        });

        return response()->json($tickets, 200);
    }

    /**
     * Get all tickets for organizer's events (paginated)
     */
    public function getAllTickets(Request $request)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $eventIds = LinkUpEvent::where('organizer_id', $organizerProfile->id)->pluck('id');

        $query = Ticket::whereIn('event_id', $eventIds)->with(['event', 'extraSetting']);

        // Apply filters
        if ($request->filled('search')) {
            $query->filter(['search' => $request->search]);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_free')) {
            $query->where('is_free', $request->is_free);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);

        if ($request->get('paginate', true)) {
            $tickets = $query->paginate($perPage);
            $blocksByTicketId = $this->wellnessBlocksByDateForTicketIds($tickets->getCollection()->pluck('id')->all());
            $tickets->getCollection()->each(function($ticket) use ($blocksByTicketId) {
                $ticket->append(['cookout', 'wellness']);
                $ticket->setAttribute('blocksByDate', $blocksByTicketId->get($ticket->id, (object) []));
                $ticket->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticket));
                $ticket->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticket));
                $ticket->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticket));
                $this->hideExtraSettingFromTicket($ticket);
            });
        } else {
            $tickets = $query->get();
            $blocksByTicketId = $this->wellnessBlocksByDateForTicketIds($tickets->pluck('id')->all());
            $tickets->each(function($ticket) use ($blocksByTicketId) {
                $ticket->append(['cookout', 'wellness']);
                $ticket->setAttribute('blocksByDate', $blocksByTicketId->get($ticket->id, (object) []));
                $ticket->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticket));
                $ticket->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticket));
                $ticket->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticket));
                $this->hideExtraSettingFromTicket($ticket);
            });
        }

        return response()->json([
            'success' => true,
            'tickets' => $tickets
        ]);
    }

    /**
     * Get tickets for a specific event
     */
    public function getEventTickets($eventId)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $event = LinkUpEvent::where('id', $eventId)
            ->where('organizer_id', $organizerProfile->id)
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found'
            ], 404);
        }

        $tickets = Ticket::where('event_id', $eventId)
            ->with(['event', 'extraSetting'])
            ->get();

        $blocksByTicketId = $this->wellnessBlocksByDateForTicketIds($tickets->pluck('id')->all());
        $tickets->each(function($ticket) use ($blocksByTicketId) {
            $ticket->append(['cookout', 'wellness']);
            $ticket->setAttribute('blocksByDate', $blocksByTicketId->get($ticket->id, (object) []));
            $ticket->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticket));
            $ticket->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticket));
            $ticket->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticket));
            $this->hideExtraSettingFromTicket($ticket);
        });

        return response()->json([
            'success' => true,
            'event' => $event,
            'tickets' => $tickets
        ]);
    }

    /**
     * Get a specific ticket
     */
    public function show($id)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $ticket = Ticket::with(['event', 'extraSetting'])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Verify ticket belongs to organizer's event
        if ($ticket->event->organizer_id != $organizerProfile->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 403);
        }

        $ticket->append(['cookout', 'wellness']);
        $ticket->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticket));
        $ticket->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticket));
        $ticket->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticket));
        $this->hideExtraSettingFromTicket($ticket);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'blocksByDate' => $this->wellnessBlocksByDateForTicket($ticket->id),
        ]);
    }

    /**
     * Create or update a ticket
     */
    public function storeOrUpdate(Request $request)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'nullable',
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'description' => 'nullable|string',
            'has_table' => 'required|in:yes,no',
            'table_price' => 'nullable|numeric|min:0',
            'table_capacity' => 'nullable|integer|min:0',
            'sections' => 'nullable|array',
            'is_free' => 'required|in:yes,no',
            'price' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'tickets_per_attendee' => 'integer|min:1',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'status' => 'required|in:active,inactive',
            'drink_addons' => 'nullable|array',
            'main_bottles' => 'nullable|array',
            'chasers_or_mixers' => 'nullable|array',
            'water_options' => 'nullable|array',
            'package_id' => 'nullable|exists:drink_packages,id',
            'cookout' => 'nullable|array',
            'wellness' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (isset($validated['is_free']) && $validated['is_free'] == 'yes') {
            $validated['price'] = 0;
        }
        if (isset($validated['has_table']) && $validated['has_table'] == 'no') {
            $validated['package_id'] = null;
        }

        // Verify event belongs to organizer
        $event = LinkUpEvent::where('id', $validated['event_id'])
            ->where('organizer_id', $organizerProfile->id)
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found or unauthorized'
            ], 403);
        }

        if (!empty($validated['id'])) {
            // Update existing ticket
            $ticket = Ticket::find($validated['id']);

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ticket not found. To create a new ticket, remove the "id" parameter.'
                ], 404);
            }

            // Verify ticket belongs to organizer's event (ownership check)
            $ticketEvent = LinkUpEvent::where('id', $ticket->event_id)
                ->where('organizer_id', $organizerProfile->id)
                ->first();
                
            if (!$ticketEvent) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $prevTotal = (int) ($ticket->quantity ?? 0);
            $prevAvailable = (int) ($ticket->qty_available ?? $prevTotal);
            $newTotal = (int) ($validated['quantity'] ?? $prevTotal);
            $delta = $newTotal - $prevTotal;
            $validated['qty_available'] = max(0, $prevAvailable + $delta);

            $ticket->update($validated);
            $message = "Ticket updated successfully";
        } else {
            // Create new ticket
            $validated['qty_available'] = (int) ($validated['quantity'] ?? 0);
            $ticket = Ticket::create($validated);
            $message = "Ticket created successfully";
        }

        // Handle ticket extras (cookout/wellness)
        if (array_key_exists('cookout', $validated) || array_key_exists('wellness', $validated)) {
            $extrasData = [];
            if (array_key_exists('cookout', $validated)) {
                $extrasData['cookout'] = $validated['cookout'];
            }
            if (array_key_exists('wellness', $validated)) {
                $extrasData['wellness'] = $validated['wellness'];
            }

            TicketExtraSetting::updateOrCreate(
                ['ticket_id' => $ticket->id],
                $extrasData
            );
        }

        $ticketResource = $ticket->load(['event', 'extraSetting'])->append(['cookout', 'wellness'])->makeHidden(['extraSetting', 'extra_setting']);
        $ticketResource->setAttribute('wellnessBookingTotal', $this->wellnessBookingTotalForTicket($ticketResource));
        $ticketResource->setAttribute('cookoutCheckoutTotal', $this->cookoutCheckoutTotalForTicket($ticketResource));
        $ticketResource->setAttribute('checkoutTotal', $this->checkoutTotalForTicket($ticketResource));

        return response()->json([
            'success' => true,
            'message' => $message,
            'ticket' => $ticketResource,
            'blocksByDate' => $this->wellnessBlocksByDateForTicket($ticket->id),
        ], !empty($validated['id']) ? 200 : 201);
    }

    /**
     * Delete a ticket
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $ticket = Ticket::with('event')->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Verify ticket belongs to organizer's event
        if ($ticket->event->organizer_id != $organizerProfile->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 403);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket deleted successfully'
        ]);
    }
}
