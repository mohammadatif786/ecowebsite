<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\LinkUpEvent;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class TicketSaleController extends Controller
{
    public function index()
    {
        $events = LinkUpEvent::all();
        $totalTicketSales = TicketSale::count();
        $vipTicketSales = TicketSale::where('ticket_type', 'VIP')->count();
        $economyTicketSales = TicketSale::where('ticket_type', 'economy')->count();
        $ticketSale = TicketSale::paginate(10);
        $countries = Country::get();
        return Inertia::render('admin/events/ticketSale/Index', [
            'events' => $events,
            'showReport' => 'no',
            'ticketSale' => $ticketSale,
            'totalTicketSales' => $totalTicketSales,
            'vipTicketSales'=> $vipTicketSales,
            'economyTicketSales'=> $economyTicketSales,
            'countries' => $countries
        ]);
    }

    public function report(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required|string',
            'country' => 'nullable|string',
            'from' => 'required|date',
            'to' => 'required|date',
            'link_up_event_id' => 'nullable',
        ]);

        $baseQuery = TicketSale::query()
            ->with('user')
            ->when($request->from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->from);
            })
            ->when($request->to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->to);
            })
            ->when($request->ticket_type && strtolower($request->ticket_type) !== 'all', function ($query) use ($request) {
                $query->where('ticket_type', $request->ticket_type);
            })
            ->when($request->link_up_event_id && strtolower($request->link_up_event_id) !== 'all', function ($query) use ($request) {
                $query->where('link_up_event_id', $request->link_up_event_id);
            });

        $ticketSale = (clone $baseQuery)->paginate(100);

        $totalTicketSales = (clone $baseQuery)->count();
        $vipTicketSales = (clone $baseQuery)->where('ticket_type', 'VIP')->count();
        $economyTicketSales = (clone $baseQuery)->where('ticket_type', 'economy')->count();

        $events = LinkUpEvent::all();
        $countries = Country::get();

        return Inertia::render('admin/events/ticketSale/Index', [
            'ticketSale' => $ticketSale,
            'events' => $events,
            'countries' => $countries,
            'totalTicketSales' => $totalTicketSales,
            'vipTicketSales' => $vipTicketSales,
            'economyTicketSales' => $economyTicketSales,
            'showReport' => 'yes'
        ]);
    }
}
