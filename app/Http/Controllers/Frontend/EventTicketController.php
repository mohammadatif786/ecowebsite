<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventTicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::whereHas('event', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })->get();

        return Inertia::render('User/Ticket/Index', [
            'tickets' => $tickets
        ]);
    }

    public function create()
    {
        $events = auth()->user()->events;
        return Inertia::render('User/Ticket/CreateTicket', [
            'events' => $events
        ]);
    }

    public function eticket()
    {
        return Inertia::render('User/Eticket');
    }

    public function buyTicket()
    {
        return Inertia::render('User/BuyTicket');
    }
    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'is_free' => 'required|boolean',
            'sales_start' => 'required|date|before_or_equal:sales_end',
            'sales_end' => 'required|date|after_or_equal:sales_start',
            'price' => 'required_if:is_free,false',
            'min_qty_per_order' => 'nullable|integer',
            'max_qty_per_order' => 'nullable',
            'link_up_event_id' => 'nullable',
            'description' => 'nullable|string',
        ]);
        if ($data['is_free'] == true) {
            $data['price'] = 0;
        }
        $data['qty_available'] = $data['qty'];
        $data['qrcode'] = time();

        Ticket::create($data);
        return redirect()->route('frontend.ticket.index')->with('message', 'Ticket created successfully.');
    }
    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        return Inertia::render('User/Ticket/TicketDetails', [
            'ticket' => $ticket
        ]);
    }
    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $events = auth()->user()->events;
        return Inertia::render('User/Ticket/EditTicket', [
            'ticket' => $ticket,
            'events' => $events,
        ]);
    }
    public function update(Request $request, Ticket $ticket)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'is_free' => 'required|boolean',
            'sales_start' => 'required|date|before_or_equal:sales_end',
            'sales_end' => 'required|date|after_or_equal:sales_start',
            'price' => 'required_if:is_free,false',
            'min_qty_per_order' => 'nullable|integer',
            'max_qty_per_order' => 'nullable',
            'link_up_event_id' => 'nullable',
            'description' => 'nullable|string',
        ]);
        if ($data['is_free'] == true) {
            $data['price'] = 0;
        }
        $data['qty_available'] = $data['qty'];
        $data['qrcode'] = time();
        $ticket->update($data);
        return redirect()->route('frontend.ticket.index')->with('message', 'Successfully Updated');
    }
    public function destroy($id)
    {
        $data = Ticket::findOrfail($id);
        $data->delete();
        return redirect()->route('frontend.ticket.index')->with('message', 'Successfully Deleted');
    }
}
