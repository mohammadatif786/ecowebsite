<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Mail\TicketResendMail;
use App\Mail\SendEmailToUsers;
use App\Models\DrinkPackage;
use App\Models\EventFee;
use App\Models\EventFeeSetting;
use App\Models\Notification;
use App\Models\ScanSignUser;
use App\Models\TicketCheckin;
use App\Services\EventInventoryService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;

class EventReportController extends Controller
{
    private $drinkInventory;

    public function __construct(EventInventoryService $eventInventoryService)
    {

        $this->drinkInventory = $eventInventoryService;
    }
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
            "status" => true,
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
     * Export attendees data
     */
    public function export(Request $request, LinkUpEvent $event)
    {
        // Get filtered attendees based on request parameters
        $query = TicketSale::with(['user', 'event'])
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

        // File info
        $fileName = 'attendees_' . $event->slug . '_' . now()->format('Ymd_His') . '.csv';
        $filePath = "exports/{$fileName}";

        // Open stream
        $stream = fopen('php://temp', 'w+');

        // CSV headers
        fputcsv($stream, [
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

        foreach ($attendees as $attendee) {
            fputcsv($stream, [
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
                $attendee->ticket_status === 'checked_in' ? 'Yes' : 'No',
                $attendee->ticket_status === 'checked_in'
                    ? $attendee->updated_at->format('Y-m-d H:i:s')
                    : 'N/A'
            ]);
        }

        rewind($stream);

        // Save file
        Storage::disk('public')->put($filePath, stream_get_contents($stream));
        fclose($stream);

        // Generate public URL
        $downloadUrl = Storage::disk('public')->url($filePath);

        return response()->json([
            'success' => true,
            'file_name' => $fileName,
            'download_url' => $downloadUrl,
        ]);
    }

    public function print(Request $request, LinkUpEvent $event)
    {
        $attendeeIds = explode(',', $request->get('attendee_ids', ''));
        $attendees = TicketSale::with(['user', 'event'])
            ->where('link_up_event_id', $event->id)
            ->whereIn('id', $attendeeIds)
            ->get();
        $appurl = config('app.url');

        return response()->json([
            'success' => true,
            'message' => 'Excel file generated successfully',
            'event' => $event,
            'attendees' => $attendees,
            'appURL' => $appurl,
        ]);
    }
    public function resend(Request $request, LinkUpEvent $event)
    {
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

        return response()->json([
            'success' => true,
            'message' => 'Tickets resent successfully to ' . count($attendees) . ' attendee(s).',
        ]);
    }
    /**
     * Toggle check-in status for an attendee
     */
    public function checkIn(Request $request, TicketSale $ticketSale)
    {
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

        return response()->json([
            'success' => true,
            'message' => $isAlreadyScanned ? 'Ticket already scanned' : 'Ticket scanned successfully',
        ]);
    }

    /**
     * Send message to attendees
     */
    public function message(Request $request, LinkUpEvent $event)
    {
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
                Mail::to($attendee->user->email)->send(new SendEmailToUsers($request->subject, $request->body));
            }

            if ($request->channels['sms'] ?? false) {
                // Send SMS
                // SMS::send($attendee->user->phone, $request->body);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Message sent to ' . count($attendees) . ' attendee(s).',
            'count' => count($attendees)
        ]);
    }

    /**
     * Display comprehensive statistics with filtering (for all events)
     */
    public function statistics(Request $request)
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

        return response()->json([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'statistics' => $eventStatistics,
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
    public function exportStatistics(Request $request)
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
                $checkedIn = TicketCheckin::where('event_id', $event->id)->distinct('ticket_sale_id')->count('ticket_sale_id');
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
    /**
     * Display drinks inventory report for an event
     */
    public function drinksInventory(Request $request, LinkUpEvent $event)
    {

        $allFees = EventFeeSetting::first();
        $drinkFeePct = floatval($allFees->drink_fee_pct ?? 0);
        $bottleFeePct = floatval($allFees->bottle_fee_pct ?? 0);
        $vipFeePct = floatval($allFees->vip_fee_pct ?? 0);

        $ticketSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('drink_addons')
            ->get();

        $vipPackageSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('table_addons')
            ->get()
            ->map(function ($sale) use ($vipFeePct) {
                $data = is_array($sale->package_data)
                    ? $sale->package_data
                    : (json_decode($sale->package_data, true) ?: []);

                $subtotal = (float) ($sale->tables_total != 0 ? $sale->tables_total : $sale->sub_total);
                $fee = ($subtotal * $vipFeePct) / 100;
                $gross = $subtotal + $fee;

                return [
                    'id' => $sale->id,
                    'ticket_name' => $sale->ticket_name ?? ($sale->ticket_type ?? 'Ticket'),
                    'package_name' => $data['name'] ?? 'Table',
                    'ticket_type' => $sale->ticket_type,
                    'subtotal' => $subtotal,
                    'fees' => $fee,
                    'gross' => $gross,
                    'created_at' => optional($sale->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        $vipTotals = [
            'total_price' => $vipPackageSales->sum('subtotal'),
            'total_fee' => $vipPackageSales->sum('fees'),
            'total_gross' => $vipPackageSales->sum('gross'),
        ];

        $tablePackageDrinks = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('table_addons')
            ->get()
            ->map(function ($sale) {
                $data = is_array($sale->package_data)
                    ? $sale->package_data
                    : (json_decode($sale->package_data, true) ?: []);

                $drinkAddons = is_array($sale->drink_addons)
                    ? $sale->drink_addons
                    : (json_decode($sale->drink_addons, true) ?: []);

                return [
                    'id' => $sale->id,
                    'ticket_name' => $sale->ticket_name ?? ($sale->ticket_type ?? 'Ticket'),
                    'package_name' => $data['name'] ?? 'Table',
                    'drinks' => $data,
                    'table_drink_addons' => $drinkAddons,
                    'created_at' => optional($sale->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        $drinkInventory = $this->drinkInventory->getInventoryFromTickets($event);

        $drinkInventory = $this->drinkInventory->calculateSales($ticketSales, $drinkInventory);

        $metrics = $this->drinkInventory->calculateDrinkMetrics($drinkInventory);
        $individualDrinks = array_values($metrics['individualDrinks']);
        $drinkCategories = array_values($metrics['drinkCategories']);

        $drinkPackages = DrinkPackage::where('organizer_id', $event->organizer_id)->get();
        $bottleService = array_values($this->drinkInventory->calculateBottleServiceMetrics($drinkPackages, $ticketSales));

        $lowStock = 0;
        $totalVariance = 0;
        $grossRevenue = 0.0;
        $totalFees = 0.0;
        $netRevenue = 0.0;

        $processItem = function ($item, $feePct) use (&$lowStock, &$totalVariance, &$grossRevenue, &$totalFees, &$netRevenue) {

            if (($item['remaining'] ?? 0) < 10) {
                $lowStock++;
            }
            $totalVariance += ($item['variance'] ?? 0);

            $price = floatval($item['price'] ?? 0);
            $actual = floatval($item['actual'] ?? 0);

            $baseNet = $price * $actual;
            $feeAmount = $baseNet * ($feePct / 100);

            $netRevenue += $baseNet;
            $totalFees += $feeAmount;
            $grossRevenue += ($baseNet + $feeAmount);
        };

        foreach ($individualDrinks as $drink) {

            $fees = $drink['category'] == 'Bottles' ? $bottleFeePct : $drinkFeePct;
            $processItem($drink, $fees);
        }

        foreach ($bottleService as $bottle) {
            $processItem($bottle, $bottleFeePct);
        }

        foreach ($vipPackageSales as $sale) {
            $base = floatval($sale['subtotal']);
            $feeAmount = $base * ($vipFeePct / 100);

            $netRevenue += $base;
            $totalFees += $feeAmount;
        }

        $totalItems = count($individualDrinks) + count($bottleService);

        $summary = [
            'totalItems' => $totalItems,
            'lowStock' => $lowStock,
            'variance' => $totalVariance,
            'visibleRows' => $totalItems,
            'gross' => round($grossRevenue, 2),
            'fees' => round($totalFees, 2),
            'net' => round($netRevenue, 2),
        ];

        // Calculate suggested purchase quantities
        $suggestedIndividual = collect($individualDrinks)
            ->filter(function ($drink) {
                return $drink['category'] !== 'Bottles';
            })
            ->map(function ($drink) {
                $actual = intval($drink['actual'] ?? 0);
                $required = ceil($actual * 1.2); // Actual sold + 20% contingency

                return [
                    'id' => $drink['id'],
                    'category' => $drink['category'],
                    'name' => $drink['name'],
                    'forecast' => $actual,
                    'required' => $required,
                    'suggested' => $required,
                    'status' => $required < 10 ? 'Low' : 'OK'
                ];
            })
            ->values();

        $suggestedBottle = collect($individualDrinks)
            ->filter(function ($drink) {
                return $drink['category'] == 'Bottles';
            })
            ->map(function ($bottle) {
                $actual = intval($bottle['actual'] ?? 0);
                $required = ceil($actual * 1.2); // Actual sold + 20% contingency

                return [
                    'id' => $bottle['id'] ?? null,
                    'type' => $bottle['category'] ?? $bottle['type'] ?? 'Bottle',
                    'name' => $bottle['name'],
                    'forecast' => $actual,
                    'required' => $required,
                    'suggested' => $required,
                    'status' => $required < 10 ? 'Low' : 'OK'
                ];
            })
            ->values();

        // Calculate totals
        $totalSuggestedIndividual = $suggestedIndividual->sum('required');
        $totalSuggestedBottle = $suggestedBottle->sum('required');

        $suggested = [
            'individual' => $suggestedIndividual,
            'bottle' => $suggestedBottle,
            'totals' => [
                'individual' => $totalSuggestedIndividual,
                'bottle' => $totalSuggestedBottle
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Inventory generated successfully',
            'individualDrinks' => $individualDrinks,
            'bottleService' => $bottleService,
            'drinkCategories' => $drinkCategories,
            'summary' => $summary,
            'allFees' => $allFees,
            'event' => $event->only(['id', 'title', 'slug', 'start_date']),
            'vipPackageSales' => [
                'tables' => $vipPackageSales,
                'total' => $vipTotals
            ],
            'tablePackageDrinks' => $tablePackageDrinks,
            'suggested' => $suggested,
        ]);
    }

    /**
     * Display revenue report for an event
     */
    public function revenue(Request $request, LinkUpEvent $event)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile || $event->organizer_id !== $organizerProfile->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all ticket sales for this event
        $ticketSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('drink_addons')
            ->get();

        // Get VIP package sales (table addons)
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

        // Get All Fees
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
        $drinkPackages = DrinkPackage::where('organizer_id', $event->organizer_id)->get();

        $bottleService = $this->drinkInventory->calculateBottleServiceMetrics($drinkPackages, $ticketSales);

        // Step 5: Calculate revenue data with required structure
        $revenueIndividual = [];
        $revenueBottle = [];
        $revenueTable = [];

        // Process individual drinks with revenue calculations
        foreach ($individualDrinks as $drink) {
            $price = $drink['price'] ?? 0;
            $actual = $drink['actual'] ?? 0;
            $base = $price * $actual;

            // Non-bottle items use drink_fee_pct
            $feeRate = $drink['category'] == 'Bottles' ? $allFees->bottle_fee_pct : $allFees->drink_fee_pct ?? 0;
            $drinkFee = $base * ($feeRate / 100);
            $bottleFee = 0; // Individual drinks don't have bottle fees
            $totalFees = $drinkFee + $bottleFee;
            $gross = $base + $totalFees;
            $net = $base;

            $revenueIndividual[] = [
                'id' => $drink['id'] ?? uniqid(),
                'name' => $drink['name'] ?? '',
                'price' => $price,
                'actual' => $actual,
                'gross' => $gross,
                'drinkFee' => $drinkFee,
                'bottleFee' => $bottleFee,
                'totalFees' => $totalFees,
                'net' => $net,
            ];
        }

        // Process bottle service with revenue calculations
        foreach ($bottleService as $bottle) {
            $price = $bottle['price'] ?? 0;
            $actual = $bottle['actual'] ?? 0;
            $base = $price * $actual;

            // Bottle items use bottle_fee_pct
            $feeRate = $allFees->bottle_fee_pct ?? 0;
            $drinkFee = 0; // Bottle service doesn't have regular drink fees
            $bottleFee = $base * ($feeRate / 100);
            $totalFees = $drinkFee + $bottleFee;
            $gross = $base + $totalFees;
            $net = $base;

            $revenueBottle[] = [
                'id' => $bottle['id'] ?? uniqid(),
                'name' => $bottle['name'] ?? '',
                'price' => $price,
                'actual' => $actual,
                'gross' => $gross,
                'drinkFee' => $drinkFee,
                'bottleFee' => $bottleFee,
                'totalFees' => $totalFees,
                'net' => $net,
            ];
        }

        // Process table sales with VIP fees
        $tableSales = TicketSale::where('link_up_event_id', $event->id)
            ->whereNotNull('table_addons')
            ->get();

        $tableGroups = [];
        foreach ($tableSales as $sale) {
            $data = is_array($sale->package_data) ? $sale->package_data : (json_decode($sale->package_data, true) ?: []);
            $packageName = $data['name'] ?? 'Table Package';

            if (!isset($tableGroups[$packageName])) {
                $tableGroups[$packageName] = [
                    'name' => $packageName,
                    'price' => 0,
                    'sold' => 0,
                    'totalFees' => 0,
                    'net' => 0,
                ];
            }

            $subtotal = (float) ($sale->tables_total != 0 ? $sale->tables_total : $sale->sub_total);
            $vipFeeRate = $allFees->vip_fee_pct ?? 0;
            $vipFee = $subtotal * ($vipFeeRate / 100);

            $tableGroups[$packageName]['price'] += $subtotal;
            $tableGroups[$packageName]['sold'] += $sale->no_of_tickets ?? 1;
            $tableGroups[$packageName]['totalFees'] += $vipFee;
            $tableGroups[$packageName]['net'] += $subtotal;
        }

        $revenueTable = array_values($tableGroups);

        // Calculate totals
        $individualGross = array_sum(array_column($revenueIndividual, 'gross'));
        $bottleGross = array_sum(array_column($revenueBottle, 'gross'));
        $tableFees = array_sum(array_column($revenueTable, 'totalFees'));
        $tableNet = array_sum(array_column($revenueTable, 'net'));

        $totalGross = $individualGross + $bottleGross;

        $totalFees = array_sum(array_column($revenueIndividual, 'totalFees')) +
            array_sum(array_column($revenueBottle, 'totalFees')) +
            $tableFees;
        $totalNet = array_sum(array_column($revenueIndividual, 'net')) +
            array_sum(array_column($revenueBottle, 'net')) +
            $tableNet;

        // Find top selling drink
        $topDrink = null;
        $maxSold = 0;
        foreach ($revenueIndividual as $drink) {
            if ($drink['actual'] > $maxSold) {
                $maxSold = $drink['actual'];
                $topDrink = $drink;
            }
        }

        $totals = [
            'gross' => $totalGross,
            'fees' => $totalFees,
            'net' => $totalNet,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Revenue report generated successfully',
            'revenue' => [
                'individual' => $revenueIndividual,
                'bottle' => $revenueBottle,
                'table' => $revenueTable,
                'totals' => $totals,
                'top' => $topDrink,
            ],
            'allFees' => $allFees ? $allFees->toArray() : null,
            'event' => $event->only(['id', 'title', 'slug']),
        ]);
    }

    /**
     * Get statistics for a specific event
     */
    public function eventStatistics(Request $request, $eventId)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json(['error' => 'Organizer profile not found'], 404);
        }

        $event = LinkUpEvent::where('id', $eventId)
            ->where('organizer_id', $organizerProfile->id)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $ticketSales = TicketSale::where('link_up_event_id', $event->id)->get();

        $statistics = [
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

        return response()->json([
            'event' => $event,
            'statistics' => $statistics
        ]);
    }

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

    public function printTicketSale(TicketSale $ticket_sale_id): JsonResponse
    {
        $ticket_sale_id->load('user', 'checkins');
        $group = TicketSale::query()
            ->with(['event.eventDetails', 'user'])
            ->where('id', $ticket_sale_id->id)
            ->get();

        $toNumber = fn($v) => is_numeric($v) ? (float) $v : 0.0;

        $details = $ticket_sale_id->event?->eventDetails;

        $startDate = '';
        $endDate = '';
        $startTime = '';
        $endTime   = '';

        if ($details) {
            if ($details->event_type === 'single') {
                $start = Carbon::parse($details->single_event_date);
                $startDate = $start->format('d / m / y');

                $end = Carbon::parse($details->single_start_time);
                $startTime = $end->format('H : i');

                $end = Carbon::parse($details->single_end_time);
                $endTime = $end->format('H : i');
            }

            if ($details->event_type === 'recurring') {
                $startDate = $details->recurr_start_date
                    ? Carbon::parse($details->recurr_start_date)->format('d / m / y')
                    : '';

                $endDate = $details->recurr_end_date
                    ? Carbon::parse($details->recurr_end_date)->format('d / m / y')
                    : '';
            }
        }
        $taxRate = $toNumber(
            $ticket_sale_id->event_tax
                ?? ($ticket_sale_id->event_tax ?? 0)
        );
        $baseSubtotal = max(
            $toNumber($ticket_sale_id->sub_total),
            $toNumber($ticket_sale_id->tables_total)
        );

        $drinksTotal = $toNumber($ticket_sale_id->drinks_total);
        $tablesTotal = $toNumber($ticket_sale_id->tables_total);

        $ticketAndAddonsTotal = $baseSubtotal + $drinksTotal + $tablesTotal;

        $discount = $toNumber($ticket_sale_id->coupan_amount);

        $feeSource = $group->firstWhere('fee_breakdown', '!=', null);
        $breakdown = is_array($feeSource?->fee_breakdown) ? $feeSource->fee_breakdown : [];

        $drinkFeePct = $toNumber(
            $breakdown['drink_fee_pct_rate']
                ?? ($breakdown['drink_fee_pct'] ?? 0)
        );

        $bottleFeePct = $toNumber(
            $breakdown['bottle_fee_pct_rate']
                ?? ($breakdown['bottle_fee_pct'] ?? 0)
        );

        $vipFeePct = $toNumber(
            $breakdown['vip_package_fee_pct_rate']
                ?? ($breakdown['vip_fee_pct'] ?? 0)
        );

        // If no breakdown, get VIP fee percentage from EventFeeSettings
        if ($vipFeePct == 0) {
            $fees = EventFeeSetting::first();
            $vipFeePct = $toNumber($fees->vip_fee_pct ?? 0);
        }

        $drinkFees  = 0;
        $bottleFees = 0;

        if (!empty($ticket_sale_id->drink_addons)) {
            foreach ($ticket_sale_id->drink_addons as $addon) {
                $price = $toNumber($addon['total_price'] ?? 0);
                $category = strtolower($addon['category'] ?? '');

                if (in_array($category, ['mixdrinks', 'wines', 'beers', 'waters', 'softdrinks'])) {
                    $drinkFees += $price * ($drinkFeePct / 100);
                }

                if ($category === 'bottles') {
                    $bottleFees += $price * ($bottleFeePct / 100);
                }
            }
        } else {
            $drinkFees = $drinksTotal * ($drinkFeePct / 100);
        }

        // Check if has table addons (VIP package)
        $hasTables = !empty($ticket_sale_id->table_addons);

        $isVipPackage = $hasTables; // Same logic as frontend
        $vipFee = $isVipPackage
            ? $baseSubtotal * ($vipFeePct / 100)
            : 0;

        $isRowFree =
            $baseSubtotal == 0 &&
            empty($ticket_sale_id->package_data) &&
            empty($ticket_sale_id->drink_addons) &&
            empty($ticket_sale_id->table_addons);

        // If VIP package exists, no service/processing fees
        if ($vipFee > 0) {
            $serviceFee = 0;
            $processingFee = 0;
        } else {
            $serviceFee = $toNumber($ticket_sale_id->fee);

            if ($serviceFee <= 0) {
                $serviceFee =
                    $toNumber($breakdown['service_fee_pct_amount'] ?? 0) +
                    $toNumber($breakdown['service_fee_fixed'] ?? 0);
            }

            $processingFee = $toNumber($ticket_sale_id->tax);

            if ($processingFee <= 0) {
                $processingFee =
                    $toNumber($breakdown['processing_fee_pct_amount'] ?? 0) +
                    $toNumber($breakdown['processing_fee_fixed'] ?? 0);
            }
        }

        $computedTotal = max(
            0,
            $ticketAndAddonsTotal
                + $drinkFees
                + $bottleFees
                + $vipFee
                + ($isRowFree ? 0 : ($serviceFee + $processingFee))
                + $taxRate
                - $discount
        );

        return response()->json([
            'current' => $ticket_sale_id,

            'fees' => [
                'drink_fees'      => round($drinkFees, 2),
                'bottle_fees'     => round($bottleFees, 2),
                'vip_fee'         => round($vipFee, 2),
                'service_fee'     => round($serviceFee, 2),
                'processing_fee'  => round($processingFee, 2),
                'discount'        => round($discount, 2),
                'tax_rate'        => round($taxRate, 2),
            ],
            'display' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'start_time'   => $startTime,
                'end_time'   => $endTime,
            ],
            'ticket_total' => round($baseSubtotal, 2),
            'totals' => round($computedTotal, 2),
        ]);
    }

    public function paymentDetail(TicketSale $ticket_sale_id): JsonResponse
    {
        $ticket_sale_id->load('user');
        $group = TicketSale::query()
            ->where('id', $ticket_sale_id->id)
            ->get();

        $fees = EventFeeSetting::first();

        $toNumber = fn($v) => is_numeric($v) ? (float) $v : 0.0;

        $feeSource = $group->firstWhere('fee_breakdown', '!=', null);
        $breakdown = is_array($feeSource?->fee_breakdown)
            ? $feeSource->fee_breakdown
            : [];

        // Extract VIP fee percentage
        $vipFeePct = $toNumber(
            $breakdown['vip_package_fee_pct_rate']
                ?? ($breakdown['vip_fee_pct'] ?? 0)
        );

        // If no breakdown, get VIP fee percentage from EventFeeSettings
        if ($vipFeePct == 0) {
            $vipFeePct = $toNumber($fees->vip_fee_pct ?? 0);
        }

        // Calculate base subtotal for VIP fee calculation
        $baseSubtotal = max(
            $toNumber($ticket_sale_id->sub_total),
            $toNumber($ticket_sale_id->tables_total)
        );

        // Check if has table addons (VIP package)
        $hasTables = !empty($ticket_sale_id->table_addons);

        // Calculate VIP fee based on tables
        $vipFee = 0;
        if ($hasTables && $vipFeePct > 0) {
            $vipFee = $baseSubtotal * ($vipFeePct / 100);
        }

        // If VIP package exists, no service/processing fees
        if ($vipFee > 0) {
            $serviceFeeTotal = 0;
            $processingFeeTotal = 0;
        } else {
            $serviceFeeTotal = $toNumber($breakdown['service_fee_pct_amount'] ?? 0)
                + $toNumber($breakdown['service_fee_fixed'] ?? 0);

            if ($serviceFeeTotal <= 0) {
                $serviceFeeTotal = $group->sum(fn($t) => $toNumber($t->fee));
            }

            $processingFeeTotal = $toNumber($breakdown['processing_fee_pct_amount'] ?? 0)
                + $toNumber($breakdown['processing_fee_fixed'] ?? 0);

            if ($processingFeeTotal <= 0) {
                $processingFeeTotal = $group->sum(fn($t) => $toNumber($t->tax));
            }
        }

        $drinkFeesTotal = $toNumber($breakdown['drink_fee_amount'] ?? 0);
        if ($drinkFeesTotal <= 0) {
            $drinkFeesTotal = $group->sum(fn($t) => $toNumber($t->drink_fees));
        }

        $bottleFeesTotal = $toNumber($breakdown['bottle_fee_amount'] ?? 0);
        if ($bottleFeesTotal <= 0) {
            $bottleFeesTotal = $group->sum(
                fn($t) => $toNumber($t->fee_breakdown['bottle_fee_amount'] ?? 0)
            );
        }
        // Calculate VIP fees total based on table addons
        $vipFeesTotal = 0;
        if (!empty($ticket_sale_id->table_addons) && $vipFeePct > 0) {
            $vipFeesTotal = $baseSubtotal * ($vipFeePct / 100);
        }


        $couponAmount = $toNumber($breakdown['coupon_amount'] ?? 0);
        if ($couponAmount <= 0) {
            $couponAmount = $group->sum(fn($t) => $toNumber($t->coupan_amount));
        }

        $netOrganizer = $ticket_sale_id->stripe_price - $serviceFeeTotal - $processingFeeTotal - $drinkFeesTotal - $bottleFeesTotal - $vipFeesTotal - $couponAmount;

        return response()->json([
            'current' => $ticket_sale_id,
            'feesSummary' => [
                'service_fee_total'     => round($serviceFeeTotal, 2),
                'processing_fee_total'  => round($processingFeeTotal, 2),
                'drink_fees_total'      => round($drinkFeesTotal, 2),
                'bottle_fees_total'     => round($bottleFeesTotal, 2),
                'vip_fees_total'        => round($vipFeesTotal, 2),
                'coupon_amount'         => round($couponAmount, 2),
                'net_organizer'         => round($netOrganizer, 2),
            ],
        ]);
    }

    public function orderDetail(TicketSale $ticket_sale_id): JsonResponse
    {
        $ticket_sale_id->load([
            'user',
            'event.eventDetails'
        ]);

        $group = TicketSale::query()
            ->with(['user', 'event.eventDetails'])
            ->where('stripe_id', $ticket_sale_id->stripe_id)
            ->get();

        $toNumber = fn($v) => is_numeric($v) ? (float) $v : 0.0;

        $details = $ticket_sale_id->event?->eventDetails;

        $startDate = '';
        $endDate = '';
        $startTime = '';
        $endTime   = '';

        if ($details) {
            if ($details->event_type === 'single') {
                $start = Carbon::parse($details->single_event_date);
                $startDate = $start->format('d / m / y');

                $end = Carbon::parse($details->single_start_time);
                $startTime = $end->format('H : i');

                $end = Carbon::parse($details->single_end_time);
                $endTime = $end->format('H : i');
            }

            if ($details->event_type === 'recurring') {
                $startDate = $details->recurr_start_date
                    ? Carbon::parse($details->recurr_start_date)->format('d / m / y')
                    : '';

                $endDate = $details->recurr_end_date
                    ? Carbon::parse($details->recurr_end_date)->format('d / m / y')
                    : '';
            }
        }

        $fees = EventFeeSetting::first();

        $breakdown = is_array($ticket_sale_id->fee_breakdown)
            ? $ticket_sale_id->fee_breakdown
            : [];

        // Check if has table addons (VIP package)
        $hasTables = !empty($ticket_sale_id->table_addons);

        // Extract VIP fee percentage
        $vipFeePct = $toNumber(
            $breakdown['vip_package_fee_pct_rate']
                ?? ($breakdown['vip_fee_pct'] ?? 0)
        );

        // If no breakdown, get VIP fee percentage from EventFeeSettings
        if ($vipFeePct == 0) {
            $vipFeePct = $toNumber($fees->vip_fee_pct ?? 0);
        }

        // Calculate base subtotal for VIP fee calculation
        $baseSubtotal = max(
            $toNumber($ticket_sale_id->sub_total),
            $toNumber($ticket_sale_id->tables_total)
        );

        // Calculate VIP fee based on tables
        $vipFees = 0;
        if ($hasTables && $vipFeePct > 0) {
            $vipFees = $baseSubtotal * ($vipFeePct / 100);
        }

        // If VIP package exists, no service/processing fees
        if ($vipFees > 0) {
            $serviceFee = 0;
            $processingFee = 0;
        } else {
            $serviceFee   = $toNumber($ticket_sale_id->fee);
            $processingFee = $toNumber($ticket_sale_id->tax);
        }

        // Extract drink and bottle fee percentages
        $drinkFeePct = $toNumber(
            $breakdown['drink_fee_pct_rate']
                ?? ($breakdown['drink_fee_pct'] ?? 0)
        );

        $bottleFeePct = $toNumber(
            $breakdown['bottle_fee_pct_rate']
                ?? ($breakdown['bottle_fee_pct'] ?? 0)
        );

        // Calculate drink and bottle fees dynamically
        $calculatedDrinkFees = 0;
        $calculatedBottleFees = 0;

        if (!empty($ticket_sale_id->drink_addons)) {
            foreach ($ticket_sale_id->drink_addons as $addon) {
                $price = $toNumber($addon['total_price'] ?? 0);
                $category = strtolower($addon['category'] ?? '');

                if (in_array($category, ['mixdrinks', 'wines', 'beers', 'waters', 'softdrinks'])) {
                    $calculatedDrinkFees += $price * ($drinkFeePct / 100);
                }

                if ($category === 'bottles') {
                    $calculatedBottleFees += $price * ($bottleFeePct / 100);
                }
            }
        }

        // Use calculated fees if available, otherwise fallback to breakdown values
        $drinkFees = $calculatedDrinkFees > 0 ? $calculatedDrinkFees : $toNumber($breakdown['drink_fee_amount'] ?? 0);
        $bottleFees = $calculatedBottleFees > 0 ? $calculatedBottleFees : $toNumber($breakdown['bottle_fee_amount'] ?? 0);
        $discount     = $toNumber($ticket_sale_id->coupan_amount);

        $drinksTotal = $toNumber($ticket_sale_id->drinks_total);

        // Calculate total paid dynamically based on actual fees
        $baseTotal = $baseSubtotal + $drinksTotal;
        $calculatedTotal = max(0, $baseTotal + $drinkFees + $bottleFees + $vipFees + $serviceFee + $processingFee - $discount);

        // Use calculated total if it matches closely with backend, otherwise use backend total
        $backendTotal = $toNumber($ticket_sale_id->stripe_price ?? $ticket_sale_id->total);
        $totalPaid = (abs($backendTotal - $calculatedTotal) <= 0.01 && $backendTotal > 0) ? $backendTotal : $calculatedTotal;

        return response()->json([
            'current' => $ticket_sale_id,
            'feesSummary' => [
                'service_fee_total'    => $serviceFee,
                'processing_fee_total' => $processingFee,
                'drink_fees_total'     => $drinkFees,
                'bottle_fees_total'    => $bottleFees,
                'vip_fees_total'       => $vipFees,
                'coupon_amount'        => $discount,
            ],

            'display' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'start_time'   => $startTime,
                'end_time'   => $endTime,
            ],

            'totals' => [
                'drinks_total'    => round($drinksTotal, 2),
                'total_paid'      => round($totalPaid, 2),
            ],
        ]);
    }
}
