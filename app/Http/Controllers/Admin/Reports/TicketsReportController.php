<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketsReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'ticket_type' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'link_up_event_id' => 'nullable',
        ]);

        $ticketSaleQuery = TicketSale::with(['user', 'ticket', 'event']);

        // Date filters
        if ($request->filled('from')) {
            $ticketSaleQuery->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $ticketSaleQuery->whereDate('created_at', '<=', $request->to);
        }

        // Ticket type filter
        if ($request->filled('ticket_type') && $request->ticket_type !== 'all') {
            $ticketSaleQuery->where('ticket_type', $request->ticket_type);
        }

        // Event filter
        if ($request->filled('link_up_event_id') && $request->link_up_event_id !== 'all') {
            $ticketSaleQuery->where('link_up_event_id', (int)$request->link_up_event_id);
        }

        $ticketSale = $ticketSaleQuery->paginate(100)->withQueryString();

        return Inertia::render('admin/Reports/Tickets/Index', [
            'ticketSale' => $ticketSale,
            'events' => LinkUpEvent::where('status', 'live')->get(),
            'showReport' => 'yes',
            'filters' => [
                'from' => $request->from,
                'to' => $request->to,
                'ticket_type' => $request->ticket_type ?? 'all',
                'link_up_event_id' => $request->link_up_event_id ?? 'all',
            ],
        ]);
    }


    public function downloadReport(Request $request)
    {
        $request->validate([
            'ticket_type' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'link_up_event_id' => 'nullable',
        ]);

        $ticketSaleQuery = TicketSale::with(['user', 'ticket', 'event']);

        // Date filters
        if ($request->filled('from')) {
            $ticketSaleQuery->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $ticketSaleQuery->whereDate('created_at', '<=', $request->to);
        }

        // Ticket type filter
        if ($request->filled('ticket_type') && $request->ticket_type !== 'all') {
            $ticketSaleQuery->where('ticket_type', $request->ticket_type);
        }

        // Event filter
        if ($request->filled('link_up_event_id') && $request->link_up_event_id !== 'all') {
            $ticketSaleQuery->where('link_up_event_id', (int)$request->link_up_event_id);
        }

        $ticketSale = $ticketSaleQuery->get();

        $pdf = Pdf::loadView('Reports.TicketReportDownload', [
            'title'   => 'Ticket Sales Document',
            'content' => $ticketSale,
        ]);
        return $pdf->download('Tcket_report_' . now()->format('Y-m-d') . '.pdf');
    }
}
