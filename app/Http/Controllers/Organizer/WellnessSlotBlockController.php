<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketExtraSetting;
use App\Models\WellnessSlotBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WellnessSlotBlockController extends Controller
{
    private function assertOrganizerOwnsTicket(Ticket $ticket): void
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->firstOrFail();
        $ticket->loadMissing('event:id,organizer_id');

        if (!$ticket->event || (int) $ticket->event->organizer_id !== (int) $organizer->id) {
            abort(403);
        }
    }

    public function index(Request $request, Ticket $ticket)
    {
        $this->assertOrganizerOwnsTicket($ticket);

        $data = $request->validate([
            'date' => 'nullable|date',
        ]);

        $query = WellnessSlotBlock::query()->where('ticket_id', $ticket->id);
        if (!empty($data['date'])) {
            $query->whereDate('slot_date', $data['date']);
        }

        $blocks = $query
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get(['slot_date', 'start_time', 'end_time', 'count']);

        if (!empty($data['date'])) {
            return response()->json([
                'date' => (string) $data['date'],
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

    public function store(Request $request, Ticket $ticket)
    {
        $this->assertOrganizerOwnsTicket($ticket);

        $data = $request->validate([
            'slot_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'count' => 'nullable|integer|min:1',
        ]);

        $delta = (int) ($data['count'] ?? 1);

        $row = WellnessSlotBlock::query()->firstOrNew([
            'ticket_id' => $ticket->id,
            'slot_date' => $data['slot_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
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

    public function clearDate(Request $request, Ticket $ticket)
    {
        $this->assertOrganizerOwnsTicket($ticket);

        $data = $request->validate([
            'slot_date' => 'required|date',
        ]);

        WellnessSlotBlock::query()
            ->where('ticket_id', $ticket->id)
            ->whereDate('slot_date', $data['slot_date'])
            ->delete();

        $extraSetting = TicketExtraSetting::where('ticket_id', $ticket->id)->first();
        if ($extraSetting && is_array($extraSetting->wellness)) {
            $wellness = $extraSetting->wellness;
            if (isset($wellness['booking']['slots']) && is_array($wellness['booking']['slots'])) {
                $modified = false;
                foreach ($wellness['booking']['slots'] as &$slot) {
                    $slotDate = substr($slot['key'] ?? '', 0, 10);
                    if ($slotDate === $data['slot_date']) {
                        $slot['disabled'] = 0;
                        $modified = true;
                    }
                }
                if ($modified) {
                    $extraSetting->wellness = $wellness;
                    $extraSetting->save();
                }
            }
        }

        return response()->json([
            'message' => 'Date blocks cleared',
        ], 200);
    }

    public function clearAll(Request $request, Ticket $ticket)
    {
        $this->assertOrganizerOwnsTicket($ticket);

        WellnessSlotBlock::query()
            ->where('ticket_id', $ticket->id)
            ->delete();

        $extraSetting = TicketExtraSetting::where('ticket_id', $ticket->id)->first();
        if ($extraSetting && is_array($extraSetting->wellness)) {
            $wellness = $extraSetting->wellness;
            if (isset($wellness['booking']['slots']) && is_array($wellness['booking']['slots'])) {
                $modified = false;
                foreach ($wellness['booking']['slots'] as &$slot) {
                    $slot['disabled'] = 0;
                    $modified = true;
                }
                if ($modified) {
                    $extraSetting->wellness = $wellness;
                    $extraSetting->save();
                }
            }
        }

        return response()->json([
            'message' => 'All blocks cleared',
        ], 200);
    }
}

