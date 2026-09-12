<?php

namespace App\Actions;

use App\Models\LinkUpEvent;
use App\Models\TicketSale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Builds the attendee list for the event-show page.
 *
 * SECURITY NOTE — this is the important change vs. the original code.
 * The old method built this list unconditionally for ANY visitor
 * loading the event page, including anonymous ones. That means every
 * attendee's name, email, and avatar was sent to the browser as
 * Inertia page props on every page load — visible in dev tools/page
 * source to literally anyone with the event URL, not just people who
 * could see it rendered in the UI.
 *
 * This version gates the whole thing behind EventPolicy::viewAttendees().
 * That policy is currently a placeholder — go adjust it to your real
 * ownership rule, then register it in AuthServiceProvider:
 *   protected $policies = [LinkUpEvent::class => EventPolicy::class];
 */
class BuildAttendeeListAction
{
    public function handle(LinkUpEvent $event): Collection
    {
        $user = Auth::user();

        if (! $user || ! $user->can('viewAttendees', $event)) {
            return collect();
        }

        return TicketSale::where('link_up_event_id', $event->id)
            ->where('ticket_status', 'confirmed')
            ->with(['user:id,name,email,avatar'])
            ->select('user_id', 'ticket_name', 'no_of_tickets', 'created_at')
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $userTickets) {
                $purchaser = $userTickets->first()->user;

                return [
                    'id' => $purchaser->id,
                    'name' => $purchaser->name,
                    'email' => $purchaser->email,
                    'avatar' => $purchaser->avatar,
                    'total_tickets' => $userTickets->sum('no_of_tickets'),
                    'purchase_date' => $userTickets->first()->created_at,
                    'tickets' => $userTickets->map(fn($ticket) => [
                        'name' => $ticket->ticket_name,
                        'quantity' => $ticket->no_of_tickets,
                    ]),
                ];
            })
            ->values();
    }
}
