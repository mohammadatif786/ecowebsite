<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\TicketSale;
use App\Models\EventFeeSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class ScannerController extends Controller
{

    public function appSetting()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $settings = \App\Models\OrganizerSetting::firstOrCreate(['organizer_id' => $organizer->id]);

        return Inertia::render('organizer/Scanner/AppSetting', [
            'settings' => $settings
        ]);
    }

    public function appSettingStore(Request $request)
    {
        $request->validate([
            'show_stats' => 'required',
            'tap_checkin' => 'required',
        ]);

        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();

        \App\Models\OrganizerSetting::updateOrCreate(
            ['organizer_id' => $organizer->id],
            [
                'show_stats' => $request->boolean('show_stats'),
                'tap_checkin' => $request->boolean('tap_checkin'),
            ]
        );

        return redirect()->back()->withSuccess('Scanner app settings saved successfully');
    }
    public function index()
    {
        $this->authorize('view', ScanSignUser::class);

        $user = Auth::user();
        $scanners = ScanSignUser::where('user_id', $user->id)
            ->with('roles')
            ->latest()
            ->get();

        return Inertia::render('organizer/Scanner/Index', [
            'scanners' => $scanners,
        ]);
    }

    /**
     * Show web-based scanner view for organizers.
     */
    public function scanView()
    {
        return Inertia::render('organizer/Scanner/Scan');
    }

    public function store(Request $request)
    {
        $this->authorize('create', ScanSignUser::class);

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:scan_sign_users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::id();
        $organizer = OrganizerProfile::where('user_id', $user)->first();

        ScanSignUser::create([
            'org_id' => $organizer->id,
            'user_id' => $user,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->withSuccess('Scanner created successfully');
    }

    public function update(Request $request, ScanSignUser $scanner)
    {
        $this->authorize('update', $scanner);

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $scanner->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $scanner->password,
        ]);

        return redirect()->back()->withSuccess('Scanner updated successfully');
    }

    public function destroy(ScanSignUser $scanner)
    {
        $this->authorize('delete', $scanner);

        $scanner->delete();
        return redirect()->back()->withSuccess('Scanner deleted successfully');
    }

    public function toggleStatus(ScanSignUser $scanner)
    {
        $this->authorize('updateStatus', $scanner);

        $scanner->status = !$scanner->status;
        $scanner->save();

        return redirect()->back()->withSuccess('Scanner status updated successfully');
    }

    /**
     * Scan ticket QR code from web (organizer panel) using the same code
     * format as the mobile scanner API.
     */
    public function scanTicket(Request $request)
    {
        $this->authorize('scanTicket', ScanSignUser::class);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'scanner_email' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $code = $request->input('code');
        $scannerEmail = $request->input('scanner_email');

        $ticketSale = TicketSale::with(['event', 'event.eventDetails', 'ticket', 'user', 'checkins'])
            ->where(function ($q) use ($code) {
                $q->where('ticket_qrcode_id', $code)
                    ->orWhere('ticket_qrcode', $code);
            })
            ->first();

        if (! $ticketSale) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unknown QR code'
            ], 404);
        }

        // Get event fee settings for fee calculations
        $eventFeeSettings = null;
        if (Schema::hasColumn('event_fee_settings', 'link_up_event_id')) {
            $eventFeeSettings = EventFeeSetting::where('link_up_event_id', $ticketSale->link_up_event_id)->first();
        }
        if (!$eventFeeSettings) {
            $eventFeeSettings = EventFeeSetting::first();
        }

        // Aggregate fees from all tickets in the same purchase to match cart totals
        $serviceFeeTotal = floatval($ticketSale->fee ?? 0);
        $processingFeeTotal = floatval($ticketSale->tax ?? 0);

        // Check if this is part of a multi-ticket purchase and aggregate fees
        if (! empty($ticketSale->stripe_id)) {
            $siblingSales = TicketSale::where('stripe_id', $ticketSale->stripe_id)->get();
            if ($siblingSales->count() > 0) {
                $aggService = $siblingSales->sum(function ($t) {
                    return floatval($t->fee ?? 0);
                });
                $aggProcessing = $siblingSales->sum(function ($t) {
                    return floatval($t->tax ?? 0);
                });
                // Use aggregated fees if available (matches cart totals)
                if ($aggService > 0) {
                    $serviceFeeTotal = $aggService;
                }
                if ($aggProcessing > 0) {
                    $processingFeeTotal = $aggProcessing;
                }
            }
        }

        $drinkFeesTotal = 0;
        $bottleFeesTotal = 0;
        $vipFeesTotal = 0;
        $couponAmount = floatval($ticketSale->coupan_amount ?? 0);
        $eventTaxTotal = floatval($ticketSale->event_tax ?? 0);

        if ($eventFeeSettings) {
            // Calculate drink/bottle fees from drink_addons
            $drinkPct = floatval($eventFeeSettings->drink_fee_pct ?? 0) / 100.0;
            $bottlePct = floatval($eventFeeSettings->bottle_fee_pct ?? 0) / 100.0;

            $addons = is_array($ticketSale->drink_addons) ? $ticketSale->drink_addons : [];
            foreach ($addons as $addon) {
                $cat = $addon['category'] ?? '';
                $totalPrice = floatval($addon['total_price'] ?? 0);
                if (in_array($cat, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks'])) {
                    $drinkFeesTotal += $totalPrice * $drinkPct;
                } elseif ($cat === 'bottles') {
                    $bottleFeesTotal += $totalPrice * $bottlePct;
                }
            }

            // VIP fee calculation
            $vipPct = floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100.0;
            if ($vipPct > 0) {
                $pkg = $ticketSale->package_data;
                $isVip = is_array($pkg) && isset($pkg['name']) && strcasecmp($pkg['name'], 'VIP Package') === 0;
                if ($isVip) {
                    $vipTicketSubtotal = floatval($ticketSale->sub_total ?? 0);
                    $vipFeesTotal = $vipTicketSubtotal * $vipPct;
                }
            }
        }

        $ticketsSubtotal = floatval($ticketSale->sub_total ?? 0);
        $tablesTotal = floatval($ticketSale->tables_total ?? 0);
        $drinksTotal = floatval($ticketSale->drinks_total ?? 0);

        // Split drinks by category
        $drinksBase = 0;
        $bottlesBase = 0;
        $addons = is_array($ticketSale->drink_addons) ? $ticketSale->drink_addons : [];
        foreach ($addons as $addon) {
            $cat = $addon['category'] ?? '';
            $totalPrice = floatval($addon['total_price'] ?? 0);
            if ($cat === 'bottles') {
                $bottlesBase += $totalPrice;
            } else {
                $drinksBase += $totalPrice;
            }
        }

        $grandTotal = floatval($ticketSale->stripe_price ?? $ticketSale->total ?? 0);

        $eventDate = [
            'type' => null,
            'stat_date' => null,
            'stat_time' => null,
            'end_date' => null,
            'end_time' => null,
        ];

        if ($ticketSale?->event?->eventDetails) {
            $eventDate['type'] = $ticketSale?->event?->eventDetails?->event_type == 'single' ? 'single' : 'recurring';
            $eventDate['stat_date'] = $ticketSale?->event?->eventDetails?->single_event_date?->format('d F, Y') ?? $ticketSale?->event?->eventDetails?->recurr_start_date?->format('d F, Y');
            $eventDate['stat_time'] = $ticketSale?->event?->eventDetails?->single_start_time?->format('H:i,A') ?? null;
            $eventDate['end_date'] =  $ticketSale?->event?->eventDetails?->recurr_end_date ? $ticketSale?->event?->eventDetails?->recurr_end_date->format('d F, Y') : null;
            $eventDate['end_time'] =  $ticketSale?->event?->eventDetails?->single_end_time?->format('H:i,A') ?? null;
        }

        // Build comprehensive response data
        $data = [
            'event_name' => $ticketSale->event?->title,
            'event_date' => $eventDate,
            'location' => [
                'venue' => $ticketSale->event?->venue,
                'city' => $ticketSale->event?->city,
                'state' => $ticketSale->event?->state,
                'country' => $ticketSale->event?->country,
                'latitude' => $ticketSale->event?->latitude,
                'longitude' => $ticketSale->event?->longtitude,
            ],
            'user_name' => $ticketSale->user?->name,
            'user_phone' => $ticketSale->user?->phone_number,
            'user_email' => $ticketSale->user?->email,
            'seats' => $ticketSale->no_of_tickets,
            'ticket_type' => $ticketSale->ticket_type,
            'ticket_name' => $ticketSale->ticket_name,
            'ticket_order_number' => $ticketSale->ticket_qrcode_id,
            'status' => $ticketSale->ticket_status,
            'payment_method' => $ticketSale->payment_method,
            'pay_type' => $ticketSale->pay_type,

            // Pricing breakdown
            'subtotal' => $ticketsSubtotal,
            'tables_total' => $tablesTotal,
            'drinks_total' => $drinksTotal,
            'coupon_discount' => $couponAmount,

            // Fees breakdown
            'service_fee' => $serviceFeeTotal,
            'processing_fee' => $processingFeeTotal,
            'drink_fees' => $drinkFeesTotal,
            'bottle_fees' => $bottleFeesTotal,
            'vip_fees' => $vipFeesTotal,
            'event_tax' => $eventTaxTotal,

            // Totals
            'grand_total' => $grandTotal,

            // Addons
            'drink_addons' => $ticketSale->drink_addons ?? [],
            'table_addons' => $ticketSale->table_addons ?? [],
            'package_data' => $ticketSale->package_data,

        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            // The scanner summary above is useful for verification, while this
            // payload powers the complete ticket-details view.
            'ticket_details' => $ticketSale,
        ], 200);
    }

    /**
     * Assign scanner role to a user.
     */
    public function assignRole(Request $request, ScanSignUser $scanner)
    {
        $this->authorize('assignRole', $scanner);

        $validated = $request->validate([
            'role' => 'required|string|in:scanner',
        ]);

        $scanner->syncRoles([$validated['role']]);

        return redirect()->back()->withSuccess('Scanner Role Assigned successfully');

    }

    /**
     * Remove scanner role and permissions from a user.
     */
    public function removeRole(ScanSignUser $scanner)
    {
        $this->authorize('assignRole', $scanner);

        $scanner->syncRoles([]);
        $scanner->syncPermissions([]);

        return redirect()->back()->withSuccess('Scanner Role and Permissions removed successfully');
    }

    /**
     * Get permissions for a scanner.
     */
    public function getPermissions(ScanSignUser $scanner): JsonResponse
    {
        $availablePermissions = Permission::where('guard_name', 'scanner')->get();
        $scannerPermissions = $scanner->getPermissionNames();
        $hasScannerRole = $scanner->hasRole('scanner');

        return response()->json([
            'available' => $availablePermissions,
            'current' => $scannerPermissions,
            'has_role' => $hasScannerRole,
        ]);
    }

    /**
     * Assign permissions to a scanner.
     */
    public function assignPermissions(Request $request, ScanSignUser $scanner)
    {
        $this->authorize('assignRole', $scanner);

        if (!$scanner->hasRole('scanner')) {
            return redirect()->back()->withErrors(['permissions' => 'You must assign the "Scanner" role to this user before assigning individual permissions.']);
        }

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $scanner->syncPermissions($validated['permissions'] ?? []);

        return redirect()->back()->withSuccess('Scanner Permissions Assigned successfully');
    }
}
