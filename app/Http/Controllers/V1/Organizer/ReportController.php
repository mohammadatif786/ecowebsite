<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get attendees report for an event
     */
    public function attendees(Request $request, LinkUpEvent $event)
    {
        // Get query parameters
        $search = $request->get('search', '');
        $sortBy = $request->get('sort', 'created_desc');
        $paymentMethods = $request->get('payment_methods', []);
        $statuses = $request->get('statuses', []);
        $fromDate = $request->get('from_date');
        $untilDate = $request->get('until_date');

        // Build query for ticket sales (attendees)
        $query = TicketSale::with(['user', 'event', 'event.eventDetails'])
            ->where('link_up_event_id', $event->id);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_qrcode_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Apply payment method filter
        if (!empty($paymentMethods)) {
            $query->whereIn('payment_method', $paymentMethods);
        }

        // Apply status filter
        if (!empty($statuses)) {
            $query->whereIn('ticket_status', $statuses);
        }

        // Apply date range filter
        if ($fromDate) {
            $query->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if ($untilDate) {
            $query->where('created_at', '<=', Carbon::parse($untilDate)->endOfDay());
        }

        // Apply sorting
        switch ($sortBy) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'order_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('ticket_status', 'asc');
                break;
            default: // created_desc
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Get paginated results
        $attendees = $query->paginate(50);

        // Calculate summary statistics
        $summaryStats = $this->calculateSummaryStats($event->id);


        return response()->json([
            'event' => $event,
            'attendees' => $attendees,
            'summaryStats' => $summaryStats,
            'filters' => $request->only(['search', 'sort', 'payment_methods', 'statuses', 'from_date', 'until_date']),
            'appURL' => config('app.url'),
        ]);
    }

    /**
     * Calculate summary statistics for the event
     */
    private function calculateSummaryStats($eventId)
    {
        $stats = TicketSale::where('link_up_event_id', $eventId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN ticket_status = "confirmed" THEN 1 END) as paid'),
                DB::raw('COUNT(CASE WHEN ticket_status = "pending" THEN 1 END) as pending'),
                DB::raw('COUNT(CASE WHEN ticket_status = "cancelled" THEN 1 END) as canceled'),
                DB::raw('SUM(CASE WHEN ticket_status = "confirmed" THEN total ELSE 0 END) as revenue')
            )
            ->first();

        // Count actual check-ins from scan records
        $actualCheckIns = TicketCheckin::where('event_id', $eventId)
            ->distinct('ticket_sale_id')
            ->count('ticket_sale_id');

        return [
            'total' => $stats->total ?? 0,
            'paid' => $stats->paid ?? 0,
            'pending' => $stats->pending ?? 0,
            'canceled' => $stats->canceled ?? 0,
            'checked_in' => $actualCheckIns, // Count based on actual scans
            'revenue' => number_format($stats->revenue ?? 0, 2),
        ];
    }


    /**
     * Get statistics for an event
     */
    public function statistics($eventId)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found'
            ], 404);
        }

        // Get comprehensive statistics
        $stats = $this->getEventStatistics($event);

        return response()->json([
            'success' => true,
            'event' => $event,
            'statistics' => $stats
        ]);
    }

    /**
     * Export attendees as CSV
     */
    public function exportAttendees($eventId, Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found'
            ], 404);
        }

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

    /**
     * Get attendees for printing tickets
     */
    public function printTickets($eventId, Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        // Verify the event belongs to the organizer
        $event = LinkUpEvent::where('id', $eventId)
                           ->where('organizer_id', $organizer->id)
                           ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found'
            ], 404);
        }

        $attendeeIds = $request->input('attendee_ids', []);

        // Get selected attendees or all if none selected
        if (empty($attendeeIds)) {
            $attendees = $this->getAttendeesQuery($event, $request)->get();
        } else {
            $attendees = $this->getAttendeesQuery($event, $request)
                              ->whereIn('id', $attendeeIds)
                              ->get();
        }

        return response()->json([
            'success' => true,
            'event' => $event,
            'attendees' => $attendees
        ]);
    }

    /**
     * Get sales summary across all events
     */
    public function salesSummary(Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        // Get date range from request or default to last 30 days
        $fromDate = $request->get('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));

        // Get all events for the organizer
        $eventsQuery = LinkUpEvent::where('organizer_id', $organizer->id);

        if ($request->filled('event_id')) {
            $eventsQuery->where('id', $request->input('event_id'));
        }

        $events = $eventsQuery->get();

        $totalRevenue = 0;
        $totalTickets = 0;
        $eventStats = [];

        foreach ($events as $event) {
            $ticketSales = TicketSale::where('link_up_event_id', $event->id)
                ->where('ticket_status', 'paid')
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);

            $revenue = $ticketSales->sum('total');
            $tickets = $ticketSales->sum('no_of_tickets');

            $totalRevenue += $revenue;
            $totalTickets += $tickets;

            $eventStats[] = [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'revenue' => $revenue,
                'tickets_sold' => $tickets
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_tickets' => $totalTickets,
                'total_events' => count($events),
                'date_range' => [
                    'from' => $fromDate,
                    'to' => $toDate
                ]
            ],
            'events' => $eventStats
        ]);
    }

    /**
     * Get revenue trends over time
     */
    public function revenueTrends(Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $days = $request->get('days', 30);
        $eventId = $request->get('event_id');

        $query = TicketSale::whereHas('event', function($q) use ($organizer) {
            $q->where('organizer_id', $organizer->id);
        })
        ->where('ticket_status', 'paid')
        ->where('created_at', '>=', now()->subDays($days));

        if ($eventId) {
            $query->where('link_up_event_id', $eventId);
        }

        $trends = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'trends' => $trends,
            'period' => [
                'days' => $days,
                'from' => now()->subDays($days)->format('Y-m-d'),
                'to' => now()->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Build attendees query with filters
     */
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

    /**
     * Get attendees summary statistics
     */
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

    /**
     * Get comprehensive event statistics
     */
    private function getEventStatistics(LinkUpEvent $event)
    {
        $ticketSales = TicketSale::where('link_up_event_id', $event->id)->get();

        return [
            'total_sales' => $ticketSales->count(),
            'total_revenue' => $ticketSales->where('ticket_status', 'confirmed')->sum('total'),
            'status_breakdown' => $ticketSales->groupBy('ticket_status')->map->count(),
            'payment_methods' => $ticketSales->groupBy('payment_method')->map->count(),
            'daily_sales' => $ticketSales->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })->map->count(),
            'checked_in_count' => TicketCheckin::where('event_id', $event->id)->count(),
            'pending_count' => $ticketSales->where('ticket_status', 'pending')->count(),
            'average_ticket_price' => $ticketSales->where('ticket_status', 'confirmed')->avg('total'),
        ];
    }
}
