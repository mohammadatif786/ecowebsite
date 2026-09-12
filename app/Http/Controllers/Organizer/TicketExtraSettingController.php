<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketExtraSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketExtraSettingController extends Controller
{
    private function assertOrganizerOwnsTicket(Ticket $ticket): void
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->firstOrFail();
        $ticket->loadMissing('event:id,organizer_id');

        if (!$ticket->event || (int) $ticket->event->organizer_id !== (int) $organizer->id) {
            abort(403);
        }
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'integer',
        ]);

        $organizer = OrganizerProfile::where('user_id', Auth::id())->firstOrFail();

        $ticketIds = Ticket::query()
            ->whereIn('id', $data['ticket_ids'])
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

    public function upsert(Request $request, Ticket $ticket)
    {
        $this->assertOrganizerOwnsTicket($ticket);

        $data = $request->validate([
            'cookout' => 'nullable|array',
            'wellness' => 'nullable|array',
        ]);

        $row = TicketExtraSetting::updateOrCreate(
            ['ticket_id' => $ticket->id],
            [
                'cookout' => $data['cookout'] ?? null,
                'wellness' => $data['wellness'] ?? null,
            ]
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
}

