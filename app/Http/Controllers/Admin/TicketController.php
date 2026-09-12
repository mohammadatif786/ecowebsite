<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventTicketDrink;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function evetTikets(Request $request, $id)
    {
        $event = LinkUpEvent::findOrFail($id);

        $organizerProfile = OrganizerProfile::find($event->organizer_id);
        $organizerUserId = $organizerProfile?->user_id;

        $mix_drinkis = EventTicketDrink::where('type', 'mixDrinks')
            ->when($organizerUserId, fn($q) => $q->where('created_by', $organizerUserId))
            ->get();
        $wines = EventTicketDrink::where('type', 'wines')
            ->when($organizerUserId, fn($q) => $q->where('created_by', $organizerUserId))
            ->get();
        $waters = EventTicketDrink::where('type', 'waters')
            ->when($organizerUserId, fn($q) => $q->where('created_by', $organizerUserId))
            ->get();
        $beers = EventTicketDrink::where('type', 'beers')
            ->when($organizerUserId, fn($q) => $q->where('created_by', $organizerUserId))
            ->get();
        $soft_drinks = EventTicketDrink::where('type', 'softDrinks')
            ->when($organizerUserId, fn($q) => $q->where('created_by', $organizerUserId))
            ->get();

        return Inertia::render('admin/events/tickets/Index', [
            'mix_drinkis' => $mix_drinkis,
            'wines' => $wines,
            'waters' => $waters,
            'beers' => $beers,
            'soft_drinks' => $soft_drinks,
            'events' => collect([$event])->map->only(['id','title'])->values(),
            'filters' => $request->only('search'),
            'message' => session('message'),
            'eventId' => (string) $id,
            'event' => $event->only(['id','title'])
        ]);
    }
    public function index(Request $request)
    {
        //    $tickets=Ticket::
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'is_free' => 'required|boolean',
            'sales_start' => 'required|date|before_or_equal:sales_end',
            'sales_end' => 'required|date|after_or_equal:sales_start',
            'price' => 'required_if:is_free,false',
            'min_qty_per_order' => 'required|integer|min:1',
            'max_qty_per_order' => 'required|integer|gte:min_qty_per_order',
            'link_up_event_id' => 'nullable',
            'description' => 'nullable|string',
            'ticket_type' => 'required|string',
        ]);

        $data['qty_available'] = $data['qty'];
        $data['qrcode'] = time();
        Ticket::create($data);
        // return redirect()->route('admin.event.tickets')->with('message', 'Ticket created successfully.');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $ticket = Ticket::findOrfail($id);
        $qrCode = QrCode::size(300)->generate($ticket->name);
        $base64Svg = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        return Inertia::render('admin/events/tickets/Show', [
            'ticket' => $ticket,
            'qrCode' => $base64Svg,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'is_free' => 'required|boolean',
            'sales_start' => 'required|date|before_or_equal:sales_end',
            'sales_end' => 'required|date|after_or_equal:sales_start',
            'price' => 'required_if:is_free,false',
            'min_qty_per_order' => 'required|integer|min:1',
            'max_qty_per_order' => 'required|integer|gte:min_qty_per_order',
            'link_up_event_id' => 'nullable',
            'description' => 'nullable|string',
            'ticket_type' => 'nullable|string',
        ]);

        $data = Ticket::findOrfail($id);
        $data->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Ticket::findOrfail($id);
        $data->delete();
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable',
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'description' => 'nullable|string',
            'has_table' => 'required|in:yes,no',
            'table_price' => 'numeric|min:0',
            'table_capacity' => 'integer|min:0',
            'sections' => 'nullable|array',
            'is_free' => 'required|in:yes,no',
            'price' => 'numeric|min:0',
            'promo_price' => 'numeric|min:0',
            'quantity' => 'integer|min:1',
            'tickets_per_attendee' => 'integer|min:1',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'status' => 'required|in:active,inactive',
            'drink_addons' => 'nullable|array',
            'main_bottles' => 'nullable|array',
            'chasers_or_mixers' => 'nullable|array',
            'water_options' => 'nullable|array',
        ]);

        if ($validated['is_free'] == 'yes') {
            $validated['price'] = 0;
        }

        if (!empty($validated['id'])) {
            $ticket = Ticket::find($validated['id']);
            $ticket->update($validated);
            $message = "Ticket updated successfully";
        } else {
            $ticket = Ticket::create($validated);
            $message = "Ticket created successfully";
        }

        return response()->json([
            'message' => $message,
            'ticket' => $ticket
        ], 200);
    }

    public function gettingTickets($eventId)
    {
        $tickets = Ticket::where('event_id', $eventId)->with('event')->get();
        return response()->json($tickets, 200);
    }
}
