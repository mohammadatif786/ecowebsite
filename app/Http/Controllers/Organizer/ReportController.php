<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function attendees(Request $request, $eventId)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        
        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->firstOrFail();

        // Get attendees with filtering and search
        $attendeesQuery = $this->getAttendeesQuery($event, $request);
        
        $attendees = $attendeesQuery->paginate(50)->withQueryString();
        
        // Get summary statistics
        $summaryStats = $this->getAttendeesSummary($event);
        
        return Inertia::render('organizer/event/report/Index', [
            'event' => $event,
            'attendees' => $attendees,
            'summaryStats' => $summaryStats,
            'filters' => $request->only(['search', 'status', 'payment_method', 'from_date', 'until_date']),
            'appURL' => env('APP_URL') . "/storage/",
        ]);
    }

    public function statistics($eventId)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        
        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->firstOrFail();

        // Get comprehensive statistics
        $stats = $this->getEventStatistics($event);
        
        return Inertia::render('organizer/event/report/Statistics', [
            'event' => $event,
            'statistics' => $stats,
            'appURL' => env('APP_URL') . "/storage/",
        ]);
    }

    public function exportAttendees($eventId, Request $request)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        
        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->firstOrFail();

        // Get all attendees based on filters
        $attendees = $this->getAttendeesQuery($event, $request)->get();
        
        // Export as CSV
        $filename = 'attendees-' . $event->slug . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($attendees) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Reference',
                'Name',
                'Email',
                'Ticket Type',
                'Quantity',
                'Total Amount',
                'Payment Method',
                'Status',
                'Order Date',
                'Check-in Status'
            ]);

            // CSV data rows
            foreach ($attendees as $attendee) {
                fputcsv($file, [
                    $attendee->ticket_qrcode_id ?? 'N/A',
                    $attendee->user->name ?? 'N/A',
                    $attendee->user->email ?? 'N/A',
                    $attendee->ticket_type ?? 'General',
                    $attendee->no_of_tickets ?? 1,
                    $attendee->total ?? 0,
                    $attendee->payment_method ?? 'N/A',
                    $attendee->ticket_status ?? 'pending',
                    $attendee->created_at->format('Y-m-d H:i:s'),
                    $attendee->ticket_status === 'checked_in' ? 'Checked In' : 'Not Checked In'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printTickets($eventId, Request $request)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        
        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->firstOrFail();

        $attendeeIds = $request->input('attendee_ids', []);
        
        // Get selected attendees or all if none selected
        if (empty($attendeeIds)) {
            $attendees = $this->getAttendeesQuery($event, $request)->get();
        } else {
            $attendees = $this->getAttendeesQuery($event, $request)
                              ->whereIn('id', $attendeeIds)
                              ->get();
        }
        
        return Inertia::render('organizer/event/report/PrintTickets', [
            'event' => $event,
            'attendees' => $attendees,
            'appURL' => env('APP_URL') . "/storage/",
        ]);
    }

    private function getAttendeesQuery(LinkUpEvent $event, Request $request)
    {
        $query = TicketSale::where('link_up_event_id', $event->id)
                          ->with(['user', 'event', 'ticket']);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ticket_qrcode_id', 'like', "%{$search}%")
                  ->orWhere('ticket_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('ticket_status', $request->input('status'));
        }

        // Payment method filter
        if ($request->filled('payment_method') && $request->input('payment_method') !== 'all') {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Date range filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('until_date')) {
            $query->whereDate('created_at', '<=', $request->input('until_date'));
        }

        return $query->orderBy('created_at', 'desc');
    }

    private function getAttendeesSummary(LinkUpEvent $event)
    {
        // Get summary statistics for the event
        $totalTickets = TicketSale::where('link_up_event_id', $event->id)->sum('no_of_tickets');
        $paidTickets = TicketSale::where('link_up_event_id', $event->id)->where('ticket_status', 'paid')->sum('no_of_tickets');
        $pendingTickets = TicketSale::where('link_up_event_id', $event->id)->where('ticket_status', 'pending')->sum('no_of_tickets');
        $canceledTickets = TicketSale::where('link_up_event_id', $event->id)->where('ticket_status', 'canceled')->sum('no_of_tickets');
        $checkedInTickets = TicketSale::where('link_up_event_id', $event->id)->where('ticket_status', 'checked_in')->sum('no_of_tickets');
        
        $totalRevenue = TicketSale::where('link_up_event_id', $event->id)
                                 ->where('ticket_status', 'paid')
                                 ->sum('total');

        return [
            'total' => $totalTickets,
            'paid' => $paidTickets,
            'pending' => $pendingTickets,
            'canceled' => $canceledTickets,
            'checked_in' => $checkedInTickets,
            'revenue' => $totalRevenue,
            'check_in_rate' => $totalTickets > 0 ? round(($checkedInTickets / $totalTickets) * 100, 1) : 0,
            'payment_rate' => $totalTickets > 0 ? round(($paidTickets / $totalTickets) * 100, 1) : 0,
        ];
    }

    private function getEventStatistics(LinkUpEvent $event)
    {
        $attendeesSummary = $this->getAttendeesSummary($event);
        
        // Sales over time (last 30 days)
        $salesOverTime = TicketSale::where('link_up_event_id', $event->id)
                                  ->where('ticket_status', 'paid')
                                  ->where('created_at', '>=', now()->subDays(30))
                                  ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get();

        // Payment methods breakdown
        $paymentMethods = TicketSale::where('link_up_event_id', $event->id)
                                   ->where('ticket_status', 'paid')
                                   ->selectRaw('payment_method, COUNT(*) as count')
                                   ->groupBy('payment_method')
                                   ->get();

        // Ticket types breakdown
        $ticketTypes = TicketSale::where('link_up_event_id', $event->id)
                                ->selectRaw('ticket_type, COUNT(*) as count, SUM(total) as revenue')
                                ->groupBy('ticket_type')
                                ->get();

        return [
            'attendees_summary' => $attendeesSummary,
            'sales_over_time' => $salesOverTime,
            'payment_methods' => $paymentMethods,
            'ticket_types' => $ticketTypes,
        ];
    }
}