<?php

namespace App\Http\Controllers\Organizer;

use App\Actions\MessageAttendeesBroadcastAction;
use App\DTOs\MessageAttendeesBroadcast;
use App\DTOs\MessageAttendeesBroadcastDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\sendMessageAttendeesBroadcastRequest;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\EventTicketDrink;
use App\Models\DrinkPackage;
use App\Models\EventFeeSetting;
use App\Models\TicketSale;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResendMail;
use App\Models\ScanSignUser;
use App\Models\TicketCheckin;
use App\Services\EventInventoryService;
use Illuminate\Support\Facades\Hash;

class EventReportController extends Controller
{
    private $drinkInventory;
    private $messageAttendeesBroadcastAction;

    public function __construct(
        EventInventoryService $eventInventoryService,
        MessageAttendeesBroadcastAction $messageAttendeesBroadcastAction,
    ) {

        $this->drinkInventory = $eventInventoryService;
        $this->messageAttendeesBroadcastAction = $messageAttendeesBroadcastAction;
    }
    /**
     * Display attendees report for an event
     */
    public function attendees(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        // Get query parameters
        $search = $request->get('search', '');
        $sortBy = $request->get('sort', 'created_desc');
        $paymentMethods = $request->get('payment_methods', []);
        $statuses = $request->get('statuses', []);
        $fromDate = $request->get('from_date');
        $untilDate = $request->get('until_date');

        // Build query for ticket sales (attendees)
        $query = TicketSale::with(['user', 'event', 'event.eventDetails', 'checkins'])
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

        return Inertia::render('organizer/event/report/Index', [
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
                DB::raw('COUNT(CASE WHEN ticket_status = "checked_in" THEN 1 END) as checked_in_status'),
                DB::raw('SUM(CASE WHEN ticket_status != "cancelled" THEN (CASE WHEN stripe_price > 0 THEN stripe_price ELSE total END) ELSE 0 END) as revenue')
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
            'checked_in_status' => $stats->checked_in_status ?? 0, // Keep for reference if needed
            'revenue' => number_format($stats->revenue ?? 0, 2),
        ];
    }

    /**
     * Export attendees data
     */
    public function export(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        // Get filtered attendees based on request parameters
        $query = TicketSale::with(['user', 'event', 'checkins'])
            ->where('link_up_event_id', $event->id);

        // Apply same filters as in attendees method
        $search = $request->get('search', '');
        $paymentMethods = $request->get('payment_methods', []);
        $statuses = $request->get('statuses', []);
        $fromDate = $request->get('from_date');
        $untilDate = $request->get('until_date');
        $attendeeIds = $request->get('attendee_ids');

        // Ensure arrays are properly handled
        if (is_string($paymentMethods)) {
            $paymentMethods = explode(',', $paymentMethods);
        }
        if (is_string($statuses)) {
            $statuses = explode(',', $statuses);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_qrcode_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($paymentMethods)) {
            $query->whereIn('payment_method', $paymentMethods);
        }

        if (!empty($statuses)) {
            $query->whereIn('ticket_status', $statuses);
        }

        if ($fromDate) {
            $query->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if ($untilDate) {
            $query->where('created_at', '<=', Carbon::parse($untilDate)->endOfDay());
        }

        if ($attendeeIds) {
            $ids = explode(',', $attendeeIds);
            $query->whereIn('id', $ids);
        }

        $attendees = $query->get();

        // Generate CSV
        $filename = 'attendees_' . $event->slug . '_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attendees) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Reference',
                'Event',
                'Ticket Type',
                'Number of Tickets',
                'Attendee Name',
                'Email',
                'Payment Method',
                'Status',
                'Price',
                'Order Date',
                'Checked In',
                'Check-in Time'
            ]);

            // CSV data
            foreach ($attendees as $attendee) {

                $checkinsCount = $attendee->checkins->count();

                $latestCheckin = $checkinsCount > 0
                    ? $attendee->checkins->sortByDesc('checked_in_at')->first()
                    : null;

                fputcsv($file, [
                    $attendee->ticket_qrcode_id,
                    $attendee->event->title,
                    $attendee->ticket_type ?? 'General',
                    $attendee->no_of_tickets,
                    $attendee->user->name ?? 'N/A',
                    $attendee->user->email ?? 'N/A',
                    $attendee->payment_method ?? 'N/A',
                    $attendee->ticket_status,
                    $attendee->total,
                    $attendee->created_at->format('Y-m-d H:i:s'),
                    $checkinsCount > 0 ? 'Yes' : 'No',
                    $latestCheckin
                        ? $latestCheckin->checked_in_at->format('Y-m-d H:i:s')
                        : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print tickets for selected attendees
     */
    public function print(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        $attendeeIds = explode(',', $request->get('attendee_ids', ''));
        $attendees = TicketSale::with(['user', 'event'])
            ->where('link_up_event_id', $event->id)
            ->whereIn('id', $attendeeIds)
            ->get();
        $appurl = config('app.url');

        return Inertia::render('organizer/event/report/PrintTickets', [
            'event' => $event,
            'attendees' => $attendees,
            'appURL' => $appurl,
        ]);
    }

    /**
     * Resend tickets to selected attendees
     */
    public function resend(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        $attendeeIds = $request->get('attendee_ids', []);
        $attendees = TicketSale::with(['user', 'event'])
            ->where('link_up_event_id', $event->id)
            ->whereIn('id', $attendeeIds)
            ->get();

        // Here you would implement the logic to resend tickets
        // This could involve sending emails, SMS, or push notifications
        foreach ($attendees as $attendee) {
            // Send ticket email/notification
            Mail::to($attendee->user->email)->send(new TicketResendMail($attendee));
        }

        return back()->with('success', 'Tickets resent successfully to ' . count($attendees) . ' attendee(s).');
    }

    /**
     * Toggle check-in status for an attendee
     */
    public function checkIn(Request $request, TicketSale $ticketSale)
    {
        $this->authorizeEventAccess($ticketSale->event);

        $user = Auth::user();
        if (! $ticketSale) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unknown QR code'
            ], 404);
        }

        // Check if ticket is already checked in
        $existingCheckin = TicketCheckin::where('ticket_sale_id', $ticketSale->id)->with('scanner')->first();

        $isAlreadyScanned = $existingCheckin !== null;
        $scanner = null;
        $scanner = ScanSignUser::where('email', $user->email)->where('org_id', $ticketSale?->event?->organizer_id)->first();
        if (!$scanner) {
            $scanner = ScanSignUser::create([
                'org_id' => $ticketSale?->event?->organizer_id,
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'password' => Hash::make('12345678'),
            ]);
        }
        // If not already scanned, create check-in record
        if (!$isAlreadyScanned) {
            TicketCheckin::create([
                'ticket_sale_id' => $ticketSale->id,
                'event_id' => $ticketSale->link_up_event_id,
                'scanner_id' => $scanner?->id,
                'ticket_qrcode_id' => $ticketSale->ticket_qrcode_id,
                'scanned_by_email' => $scanner->email,
                'checked_in_at' => now(),
            ]);
        }

        $message = $isAlreadyScanned ? 'Ticket already scanned' : 'Ticket scanned successfully';
        return back()->with('success', $message);
    }

    /**
     * Send message to attendees
     */
    public function message(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        $request->validate([
            'audience' => 'required|in:single,selected,filtered',
            'channels' => 'required|array',
            'channels.inApp' => 'boolean',
            'channels.email' => 'boolean',
            'subject' => 'required_if:channels.email,true',
            'body' => 'required|string',
            'attendee_id' => 'required_if:audience,single|exists:ticket_sales,id',
            'attendee_ids' => 'required_if:audience,selected|array'
        ]);

        // Get target attendees based on audience selection
        $query = TicketSale::with(['user'])->where('link_up_event_id', $event->id);

        switch ($request->audience) {
            case 'single':
                $query->where('id', $request->attendee_id);
                break;
            case 'selected':
                $query->whereIn('id', $request->attendee_ids);
                break;
            case 'filtered':
                $query->whereIn('id', $request->attendee_ids);
                break;
        }

        $attendees = $query->get();
        foreach ($attendees as $attendee) {
            if ($request->channels['inApp'] ?? false) {
                // Send in-app notification
                Notification::create([
                    'user_id' => $attendee->user->id,
                    'message' => $request->body,
                    'type' => 'message',
                    'title' => $request->subject ?? "{$event->title} Message",
                    'send_by' => auth()->user()->id,
                    'context' => 'message',
                    'unread' => true,
                    'priority' => true,
                    'icon' => 'message',
                    'avatar' => $attendee->user->avatar,
                ]);
            }

            if ($request->channels['email'] ?? false) {
                // Send email
                Mail::to($attendee->user->email)->send(new AttendeeEmail($request->subject, $request->body));
            }

            if ($request->channels['sms'] ?? false) {
                // Send SMS
                // SMS::send($attendee->user->phone, $request->body);
            }
        }

        return back()->with('success', 'Message sent to ' . count($attendees) . ' attendee(s).');
    }

    /**
     * Display statistics for an event
     */
    public function statistics(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        // Get comprehensive statistics
        $stats = $this->getEventStatistics($event);

        return Inertia::render('organizer/event/report/Statistics', [
            'event' => $event,
            'statistics' => $stats
        ]);
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

    /**
     * Display comprehensive statistics with filtering
     */
    public function Reportstatistics(Request $request)
    {
        $user = Auth::user();

        $organizer_id = OrganizerProfile::where('user_id', $user->id)->first()->id;

        // Get filter parameters
        $eventId = $request->get('event_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $status = $request->get('status');
        $paymentMethods = $request->get('payment_methods', []);
        $sortBy = $request->get('sort_by', 'event_date_desc');

        // Handle payment methods array
        if (is_string($paymentMethods)) {
            $paymentMethods = explode(',', $paymentMethods);
        }

        // Build events query
        $eventsQuery = LinkUpEvent::where('organizer_id', $organizer_id)
            ->with(['ticketSales.user', 'ticketSales.checkins']);

        // Apply filters
        if ($eventId) {
            $eventsQuery->where('id', $eventId);
        }

        if ($fromDate) {
            $eventsQuery->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }

        if ($toDate) {
            $eventsQuery->where('created_at', '<=', Carbon::parse($toDate)->endOfDay());
        }

        if ($status) {
            $eventsQuery->where('status', $status);
        }

        // Apply sorting
        switch ($sortBy) {
            case 'event_date_asc':
                $eventsQuery->orderBy('created_at', 'asc');
                break;
            case 'revenue_desc':
            case 'revenue_asc':
                // We'll sort by revenue after calculation
                $eventsQuery->orderBy('created_at', 'desc');
                break;
            case 'tickets_desc':
            case 'tickets_asc':
                // We'll sort by ticket count after calculation
                $eventsQuery->orderBy('created_at', 'desc');
                break;
            default: // event_date_desc
                $eventsQuery->orderBy('created_at', 'desc');
                break;
        }

        $events = $eventsQuery->get();

        // Calculate statistics for each event
        $eventStatistics = [];
        $totalEvents = 0;
        $totalTickets = 0;
        $totalRevenue = 0;
        $revenueOverTime = [];
        $statusBreakdown = [];
        $paymentMethodStats = [];
        $topEvents = [];

        foreach ($events as $event) {
            $ticketSales = $event->ticketSales;

            // Filter by payment methods if specified
            if (!empty($paymentMethods)) {
                $ticketSales = $ticketSales->whereIn('payment_method', $paymentMethods);
            }

            $eventRevenue = $ticketSales->where('ticket_status', 'confirmed')->sum('total');
            $eventTicketsSold = $ticketSales->count();
            $eventCheckedIn = $ticketSales->sum(function ($ticketSale) {
                return $ticketSale->checkins->count();
            });

            $eventData = [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'created_at' => $event->event_date,
                'location' => $event->location,
                'status' => $event->status,
                'total_tickets_sold' => $eventTicketsSold,
                'total_revenue' => $eventRevenue,
                'checked_in_count' => $eventCheckedIn,
            ];

            $eventStatistics[] = $eventData;

            // Aggregate totals
            $totalEvents++;
            $totalTickets += $eventTicketsSold;
            $totalRevenue += $eventRevenue;

            // Revenue over time (group by month)
            $monthKey = Carbon::parse($event->event_date)->format('Y-m');
            if (!isset($revenueOverTime[$monthKey])) {
                $revenueOverTime[$monthKey] = 0;
            }
            $revenueOverTime[$monthKey] += $eventRevenue;

            // Status breakdown
            foreach ($ticketSales->groupBy('ticket_status') as $status => $tickets) {
                if (!isset($statusBreakdown[$status])) {
                    $statusBreakdown[$status] = 0;
                }
                $statusBreakdown[$status] += $tickets->count();
            }

            // Payment methods breakdown
            foreach ($ticketSales->groupBy('payment_method') as $method => $tickets) {
                $method = $method ?: 'unknown';
                if (!isset($paymentMethodStats[$method])) {
                    $paymentMethodStats[$method] = 0;
                }
                $paymentMethodStats[$method] += $tickets->count();
            }

            // Add to top events
            if ($eventRevenue > 0) {
                $topEvents[] = [
                    'title' => $event->title,
                    'revenue' => $eventRevenue
                ];
            }
        }

        // Sort events by specified criteria
        if (in_array($sortBy, ['revenue_desc', 'revenue_asc'])) {
            usort($eventStatistics, function ($a, $b) use ($sortBy) {
                return $sortBy === 'revenue_desc' ?
                    $b['total_revenue'] <=> $a['total_revenue'] :
                    $a['total_revenue'] <=> $b['total_revenue'];
            });
        } elseif (in_array($sortBy, ['tickets_desc', 'tickets_asc'])) {
            usort($eventStatistics, function ($a, $b) use ($sortBy) {
                return $sortBy === 'tickets_desc' ?
                    $b['total_tickets_sold'] <=> $a['total_tickets_sold'] :
                    $a['total_tickets_sold'] <=> $b['total_tickets_sold'];
            });
        }

        // Sort and limit top events
        usort($topEvents, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        $topEvents = array_slice($topEvents, 0, 10);

        // Format revenue over time for charts
        $revenueChartData = [];
        ksort($revenueOverTime);
        foreach ($revenueOverTime as $month => $revenue) {
            $revenueChartData[] = [
                'date' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'revenue' => $revenue
            ];
        }

        // Calculate average revenue per event
        $avgRevenuePerEvent = $totalEvents > 0 ? $totalRevenue / $totalEvents : 0;

        // Prepare statistics array
        $statistics = [
            'summary' => [
                'total_events' => $totalEvents,
                'total_tickets' => $totalTickets,
                'total_revenue' => $totalRevenue,
                'avg_revenue_per_event' => $avgRevenuePerEvent,
            ],
            'events' => $eventStatistics,
            'charts' => [
                'revenue_over_time' => $revenueChartData,
                'status_breakdown' => $statusBreakdown,
                'payment_methods' => $paymentMethodStats,
                'top_events' => $topEvents,
            ]
        ];

        // Get all user's events for filter dropdown
        $allEvents = LinkUpEvent::where('organizer_id', $organizer_id)
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return Inertia::render('organizer/report/Statistics', [
            'statistics' => $statistics,
            'events' => $allEvents,
            'initialFilters' => [
                'event_id' => $eventId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'status' => $status,
                'payment_methods' => $paymentMethods,
                'sort_by' => $sortBy,
            ]
        ]);
    }

    /**
     * Export comprehensive statistics report
     */
    /**
     * Display drinks inventory report for an event
     */
    public function drinksInventory(Request $request, LinkUpEvent $event)
    {
        $this->authorizeEventAccess($event);

        // Get all ticket sales for this event
        $ticketSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('drink_addons')
            ->get();

        // Get VIP package sales (package_id not null and package_data name === 'VIP Package')
        $vipPackageSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('table_addons')
            ->get()
            ->map(function ($sale) {
                $data = is_array($sale->package_data) ? $sale->package_data : (json_decode($sale->package_data, true) ?: []);
                return [
                    'id' => $sale->id,
                    'ticket_name' => $sale->ticket_name ?? ($sale->ticket_type ?? 'Ticket'),
                    'package_name' => $data['name'] ?? 'Table',
                    'ticket_type' => $sale->ticket_type,
                    'subtotal' => (float) ($sale->tables_total != 0 ? $sale->tables_total : $sale->sub_total),
                    'created_at' => optional($sale->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        //Get All Fees
        $allFees = EventFeeSetting::first();
        $tablePackageDrinks = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('table_addons')
            ->get()->map(function ($sale) {
                $data = is_array($sale->package_data) ? $sale->package_data : (json_decode($sale->package_data, true) ?: []);
                return [
                    'id' => $sale->id,
                    'ticket_name' => $sale->ticket_name ?? ($sale->ticket_type ?? 'Ticket'),
                    'package_name' => $data['name'] ?? 'Table',
                    'drinks' => $sale->package_data,
                    'table_drink_addons' => $sale->drink_addons,
                    'created_at' => optional($sale->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        $individualDrinks = [];
        $bottleService = [];
        $drinkCategories = [];

        // Step 1: Build inventory from tickets (what was available)
        $drinkInventory = $this->drinkInventory->getInventoryFromTickets($event);

        // Step 2: Calculate actual sold from ticket sales
        $drinkInventory = $this->drinkInventory->calculateSales($ticketSales, $drinkInventory);

        // Step 3: Calculate metrics for each drink
        $metrics = $this->drinkInventory->calculateDrinkMetrics($drinkInventory);
        $individualDrinks = $metrics['individualDrinks'];
        $drinkCategories = $metrics['drinkCategories'];

        // Step 4: Process bottle service packages (if any)
        $drinkPackages = \App\Models\DrinkPackage::where('organizer_id', $event->organizer_id)->get();

        $bottleService = $this->drinkInventory->calculateBottleServiceMetrics($drinkPackages, $ticketSales);

        // Step 5: Calculate summary statistics
        $totalItems = count($individualDrinks) + count($bottleService);
        $lowStock = 0;
        $totalVariance = 0;

        $totalBaseRevenue = 0.0; // organiser net
        $totalFees = 0.0;        // drink/bottle fees charged to customer

        foreach ($individualDrinks as $drink) {
            if ($drink['remaining'] < 10) {
                $lowStock++;
            }
            $totalVariance += $drink['variance'];

            $base = ($drink['price'] ?? 0) * ($drink['actual'] ?? 0);
            // Non-bottle items use drink_fee_pct
            $feeRate = $allFees->drink_fee_pct ?? 0;
            $feeAmount = $base * ($feeRate / 100);

            $totalBaseRevenue += $base;
            $totalFees += $feeAmount;
        }

        foreach ($bottleService as $bottle) {
            if ($bottle['remaining'] < 10) {
                $lowStock++;
            }
            $totalVariance += $bottle['variance'];

            $base = ($bottle['price'] ?? 0) * ($bottle['actual'] ?? 0);
            // Bottle items use bottle_fee_pct
            $feeRate = $allFees->bottle_fee_pct ?? 0;
            $feeAmount = $base * ($feeRate / 100);

            $totalBaseRevenue += $base;
            $totalFees += $feeAmount;
        }

        // Gross = customer charge (base + drink/bottle fees), Net = base kept by organiser
        $grossRevenue = $totalBaseRevenue + $totalFees;
        $netRevenue = $totalBaseRevenue;

        $summary = [
            'totalItems' => $totalItems,
            'lowStock' => $lowStock,
            'variance' => $totalVariance,
            'visibleRows' => $totalItems,
            'gross' => $grossRevenue,
            'fees' => $totalFees,
            'net' => $netRevenue,
        ];

        return Inertia::render('organizer/event/report/DrinksInventory', [
            'individualDrinks' => $individualDrinks,
            'bottleService' => $bottleService,
            'drinkCategories' => $drinkCategories,
            'summary' => $summary,
            'allFees' => $allFees,
            'event' => $event->only(['id', 'title', 'slug']),
            'vipPackageSales' => $vipPackageSales,
            'tablePackageDrinks' => $tablePackageDrinks,
        ]);
    }

    /**
     * Export comprehensive statistics report
     */
    public function exportStatistics(Request $request, LinkUpEvent $event)
    {
        $user = Auth::user();

        $organizer_id = OrganizerProfile::where('user_id', $user->id)->first()->id;

        // Get filter parameters (same as Reportstatistics)
        $eventId = $request->get('event_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $status = $request->get('status');
        $paymentMethods = $request->get('payment_methods', []);

        if (is_string($paymentMethods)) {
            $paymentMethods = explode(',', $paymentMethods);
        }

        // Build events query with same filters
        $eventsQuery = LinkUpEvent::where('organizer_id', $organizer_id)
            ->with(['ticketSales.user']);

        // Apply same filters as in Reportstatistics method
        if ($eventId) {
            $eventsQuery->where('id', $eventId);
        }

        if ($fromDate) {
            $eventsQuery->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }

        if ($toDate) {
            $eventsQuery->where('created_at', '<=', Carbon::parse($toDate)->endOfDay());
        }

        if ($status) {
            $eventsQuery->where('status', $status);
        }

        $events = $eventsQuery->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'event_statistics_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($events, $paymentMethods) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Event Title',
                'Event Date',
                'Status',
                'Location',
                'Total Tickets Sold',
                'Total Revenue',
                'Checked In Count',
                'Attendance Rate (%)',
                'Pending Payments',
                'Failed Payments'
            ]);

            // CSV data
            foreach ($events as $event) {
                $ticketSales = $event->ticketSales;

                // Filter by payment methods if specified
                if (!empty($paymentMethods)) {
                    $ticketSales = $ticketSales->whereIn('payment_method', $paymentMethods);
                }

                $totalTickets = $ticketSales->count();
                $revenue = $ticketSales->where('ticket_status', 'paid')->sum('total');
                $checkedIn = $ticketSales->where('ticket_status', 'checked_in')->count();
                $pending = $ticketSales->where('ticket_status', 'pending')->count();
                $failed = $ticketSales->where('ticket_status', 'failed')->count();
                $attendanceRate = $totalTickets > 0 ? round(($checkedIn / $totalTickets) * 100, 2) : 0;

                fputcsv($file, [
                    $event->title,
                    $event->event_date ? Carbon::parse($event->event_date)->format('Y-m-d H:i:s') : 'TBD',
                    $event->status,
                    $event->location ?: 'Online',
                    $totalTickets,
                    $revenue,
                    $checkedIn,
                    $attendanceRate,
                    $pending,
                    $failed
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sendMessageAttendeesBroadcast(sendMessageAttendeesBroadcastRequest $request)
    {
        $data = $request->validated();
        $dto  = MessageAttendeesBroadcastDTO::fromRequest($data);
        $response = $this->messageAttendeesBroadcastAction->execute($dto);

        return back()->with('success', 'Message sent successfully. Sent: ' . $response->totalSent . ', Delivered: ' . $response->totalDelivered . ', Failed: ' . $response->totalFailed);
    }

    /**
     * Authorize access to the event for the current organizer
     */
    private function authorizeEventAccess(LinkUpEvent $event)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // Assuming one-to-one or one-to-many relationship where user has one organizer profile
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Unauthorized access to event report.');
        }
    }
}
