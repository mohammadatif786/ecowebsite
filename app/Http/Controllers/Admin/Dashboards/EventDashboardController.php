<?php

namespace App\Http\Controllers\Admin\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use App\Models\LinkUpEvent;
use App\Models\Ticket;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EventDashboardController extends Controller
{
    public function index()
    {
        $total_events = LinkUpEvent::count();
        $total_organizers = EventOrganizer::count();
        $event_revenue = TicketSale::sum('total');
        $event_tickets = Ticket::count();
        $events_by_country  = LinkUpEvent::select('country', DB::raw('COUNT(*) as total_events'))
            ->groupBy('country')
            ->get();

        // dd($this->EventOrganizerGraph()->toArray());
        return Inertia::render('admin/Dashboards/Event', [
            'total_events' => $total_events,
            'total_organizers' => $total_organizers,
            'event_revenue' => $event_revenue,
            'event_tickets' => $event_tickets,
            'events_by_country' => $events_by_country,
            'event_revenue_graph' => $this->EventRevenueGraph(),
            'event_organizer_graph' => $this->EventOrganizerGraph(),
        ]);
    }

    public function EventRevenueGraph()
    {
        $data = DB::table('ticket_sales as ts')
            ->join('link_up_events as le', 'ts.link_up_event_id', '=', 'le.id')
            ->selectRaw('le.country')
            ->selectRaw('COUNT(DISTINCT ts.link_up_event_id) as total_events')
            ->selectRaw('SUM(ts.total) as revenue')
            ->groupBy('le.country')
            ->orderByRaw('MIN(ts.created_at)')
            ->get();

        return $data;
    }

    public function EventOrganizerGraph()
    {
        $data = DB::table('ticket_sales as ts')
            ->join('link_up_events as le', 'ts.link_up_event_id', '=', 'le.id')
            ->selectRaw('le.country')
            ->selectRaw('COUNT(DISTINCT ts.link_up_event_id) as total_events')
            ->selectRaw('SUM(ts.total) as revenue')
            ->selectRaw('SUM(ts.fee) as fees')
            ->selectRaw('COUNT(DISTINCT le.organizer_id) as organizers')
            ->groupBy('le.country')
            ->orderByRaw('MIN(ts.created_at)')
            ->get();

        return $data;
    }
}
