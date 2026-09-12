<?php

namespace App\Actions\NewFrontend;

use App\DTOs\NewFrontend\EventIndexFilters;
use App\Models\CaribbeanIsland;
use App\Models\EventCategory;
use App\Models\EventFeeSetting;
use App\Models\Country;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\Tax;
use App\Models\TicketSale;
use App\Models\User;
use App\Repositories\NewFrontend\EventRepository;
use App\Repositories\SettingsRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Builds the full payload for the public events-index page.
 *
 * This is the ONLY place that knows "free events get excluded from
 * every other list" — that rule used to be repeated 4x in the controller.
 */
class GetEventIndexDataAction
{
    public function __construct(
        private readonly EventRepository $events,
        private readonly SettingsRepository $settings,
    ) {}

    public function handle(User $user, EventIndexFilters $filters): array
    {
        $freeEvents = $this->events->getFreeEvents($filters);
        $freeEventIds = $freeEvents->pluck('id');

        $excludeFree = fn(Collection $events): Collection => $events
            ->reject(fn($event) => $freeEventIds->contains($event->id))
            ->values();

        $ticketSales = TicketSale::with(['event.eventDetails', 'ticket', 'checkins'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        $activeGrouped = [];
        $cancelledGrouped = [];

        foreach ($ticketSales as $sale) {
            $status = strtolower((string) $sale->ticket_status);
            $isCancelled = in_array($status, ['cancelled', 'canceled'], true);
            $eventId = $sale->link_up_event_id;
            $quantity = max(1, (int) ($sale->no_of_tickets ?? 1));
            $paid = (float) ($sale->stripe_price ?? 0);
            if ($paid <= 0) {
                $paid = (float) ($sale->total ?? 0);
            }
            $eventDetails = $sale->event?->eventDetails;
            $eventStart = $sale->event?->start_time ?: $eventDetails?->single_event_date;
            $eventEnd = $sale->event?->end_time ?: $eventDetails?->single_event_date ?: $eventStart;
            $subtotal = (float) ($sale->sub_total ?? 0);
            if ($subtotal <= 0) {
                $subtotal = max(0, $paid - (float) ($sale->fee ?? 0) - (float) ($sale->event_tax ?? 0));
            }

            $ticketObj = [
                'id' => $sale->id,
                'qr' => $sale->ticket_qrcode ?: $sale->ticket_qrcode_id ?: $sale->id,
                'ticket_qrcode' => $sale->ticket_qrcode ?: $sale->ticket_qrcode_id ?: $sale->id,
                'date' => $sale->created_at ? $sale->created_at->format('M d, Y') : '',
                'created_at' => $sale->created_at,
                'checkin' => $status === 'checkin',
                'quantity' => $quantity,
                'no_of_tickets' => $quantity,
                'paid' => $paid,
                'sub_total' => $subtotal,
                'total' => $paid,
                'fee' => (float) ($sale->fee ?? 0),
                'tax' => (float) ($sale->tax ?? 0),
                'event_tax' => (float) ($sale->event_tax ?? 0),
                'discount' => (float) ($sale->discount ?? 0),
                'coupan_amount' => (float) ($sale->coupan_amount ?? 0),
                'tables_total' => (float) ($sale->tables_total ?? 0),
                'drinks_total' => (float) ($sale->drinks_total ?? 0),
                'wellness_total' => (float) ($sale->wellness_total ?? 0),
                'cookout_total' => (float) ($sale->cookout_total ?? 0),
                'drink_addons' => $sale->drink_addons ?: [],
                'table_addons' => $sale->table_addons ?: [],
                'package_data' => $sale->package_data,
                'fee_breakdown' => $sale->fee_breakdown ?: [],
                'wellness_addons' => $sale->wellness_addons ?: [],
                'cookout_included_protein' => $sale->cookout_included_protein,
                'cookout_included_sides' => $sale->cookout_included_sides ?: [],
                'cookout_addons' => $sale->cookout_addons ?: [],
                'status' => $status,
                'ticket_status' => $sale->ticket_status,
                'ticket_name' => $sale->ticket_name ?: $sale->ticket?->name ?: 'Ticket',
                'ticket_type' => $sale->ticket_type ?: $sale->ticket?->ticket_type ?: $sale->ticket?->type ?: 'Ticket',
                'checkins' => $sale->checkins->map(fn ($checkin) => [
                    'id' => $checkin->id,
                    'created_at' => $checkin->created_at,
                ])->values(),
                'ticket' => [
                    'id' => $sale->ticket?->id,
                    'name' => $sale->ticket?->name,
                    'type' => $sale->ticket?->type,
                    'ticket_type' => $sale->ticket?->ticket_type,
                    'price' => $sale->ticket?->price,
                    'table_price' => $sale->ticket?->table_price,
                    'has_table' => $sale->ticket?->has_table,
                    'wellness' => $sale->ticket?->wellness,
                    'cookout' => $sale->ticket?->cookout,
                ],
                'event' => [
                    'id' => $sale->event?->id,
                    'title' => $sale->event?->title ?? $sale->ticket_name ?? 'Event',
                    'organizer_name' => $sale->event?->organizer?->organizer_name ?? $sale->event?->organizer_name ?? 'Organizer',
                    'organizer_user_id' => $sale->event?->user_id ?? $sale->event?->organizer?->user_id,
                    'venue' => $sale->event?->venue,
                    'city' => $sale->event?->city,
                    'country' => $sale->event?->country,
                    'start_time' => $eventStart,
                    'end_time' => $eventEnd,
                    'currency_symbol' => $sale->event?->currency_symbol ?? '$',
                ],
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                ],
            ];

            $groupKey = $eventId . '_' . ($isCancelled ? 'cancelled' : 'active');
            $groupBase = [
                'event_id' => $eventId,
                'img' => $sale->event?->image_url ?: asset('assets/images/default-image.png'),
                'event' => $sale->event?->title ?? $sale->ticket_name ?? 'Event',
                'event_desc' => $sale->event?->description ?: 'LinkUp Event',
                'total' => 0,
                'ticket_count' => 0,
                'tickets' => [],
            ];

            if ($isCancelled) {
                if (!isset($cancelledGrouped[$groupKey])) {
                    $cancelledGrouped[$groupKey] = $groupBase;
                }
                $cancelledGrouped[$groupKey]['tickets'][] = $ticketObj;
                $cancelledGrouped[$groupKey]['ticket_count'] += $quantity;
                $cancelledGrouped[$groupKey]['total'] += $paid;
            } else {
                if (!isset($activeGrouped[$groupKey])) {
                    $activeGrouped[$groupKey] = $groupBase;
                }
                $activeGrouped[$groupKey]['tickets'][] = $ticketObj;
                $activeGrouped[$groupKey]['ticket_count'] += $quantity;
                $activeGrouped[$groupKey]['total'] += $paid;
            }
        }

        $extraData = [];
        if ($user->type === 'organizer' || $user->type === 'admin') {
            $organizer = OrganizerProfile::where('user_id', $user->id)->first();
            $organizerId = $organizer?->id;

            $extraData = [
                'categories' => EventCategory::all(),
                'caribbeans' => CaribbeanIsland::all(),
                'scanners' => ScanSignUser::where('user_id', $user->id)->where('status', true)->get(),
                'allEvents' => $organizerId ? LinkUpEvent::where('organizer_id', $organizerId)->get() : [],
                'appURL' => env('APP_URL') . "storage/",
            ];
        }

        return array_merge([
            'events' => $excludeFree($this->events->getFilteredEvents($filters)),
            'allCategories' => EventCategory::all(),
            'newProvidence' => $excludeFree($this->events->getTrendingInSameCity($filters)),
            'eventLowCost' => $excludeFree($this->events->getLowCostEvents(30, $filters)),
            'jamaicaEvent' => $excludeFree($this->events->getEventsInSameCountry($user, $filters)),
            'user' => $user,
            'freeEvents' => $freeEvents,
            'activeTickets' => array_values($activeGrouped),
            'cancelledTickets' => array_values($cancelledGrouped),
            'eventFeeSettings' => EventFeeSetting::first(),
            'taxRules' => $this->settings->getTaxRules(),
            'countryTaxRules' => Tax::query()
                ->select(['country', 'country_label', 'tax_type', 'tax'])
                ->get()
                ->map(fn (Tax $tax) => [
                    'country' => $tax->country,
                    'country_label' => $tax->country_label ?: $tax->country,
                    'tax_type' => $tax->tax_type,
                    'tax' => (float) $tax->tax,
                ])
                ->values(),
            'eventCountries' => Country::query()
                ->select(['name', 'code'])
                ->orderBy('name')
                ->get()
                ->map(fn (Country $country) => [
                    'name' => $country->name,
                    'code' => strtoupper((string) $country->code),
                ])
                ->values(),
        ], $extraData);
    }
}
