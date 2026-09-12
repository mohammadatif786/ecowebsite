<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventFeeSetting;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use App\Services\PopularityScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class ScannerController extends Controller
{
    /**
     * Get all scanners for the authenticated organizer
     */
    public function index()
    {
        $user = Auth::user();
        //dd($user->id);
        $scanners = ScanSignUser::where('user_id', $user->id)->with('roles','permissions')->latest()->get();
        //  dd($scanners);
        return response()->json([
            'scanners' => $scanners,
        ]);
    }
    /**
     * Mobile scanner login
     */
    public function mobileLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'firebase_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $scanner = ScanSignUser::where('email', $request->email)->first();

        if (! $scanner) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner not found'
            ], 404);
        }

        if ($scanner->status === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner is inactive'
            ], 403);
        }

        if ($request->filled('firebase_id') && empty($scanner->firebase_id)) {
            $scanner->firebase_id = $request->firebase_id;
            $scanner->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'scanner' => $scanner,
        ], 200);
    }
    /**
     * Scan ticket QR code and return ticket details similar to purchased ticket
     */
    public function scanTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'scanner_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $code = $request->code;
        $scannerEmail = $request->scanner_email;

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

        // Check if ticket is already checked in
        $existingCheckin = TicketCheckin::where('ticket_sale_id', $ticketSale->id)->with('scanner')->first();

        $isAlreadyScanned = $existingCheckin !== null;
        $scanner = null;
        if ($scannerEmail) {
            $scanner = ScanSignUser::where('email', $scannerEmail)->where('org_id', $ticketSale?->event?->organizer_id)->first();
            if (!$scanner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized scanner. You are not allowed to scan tickets for this event.',
                ], 403);
            }
        }

        // If not already scanned, create check-in record
        if (!$isAlreadyScanned) {
            TicketCheckin::create([
                'ticket_sale_id' => $ticketSale->id,
                'event_id' => $ticketSale->link_up_event_id,
                'scanner_id' => $scanner?->id,
                'ticket_qrcode_id' => $ticketSale->ticket_qrcode_id,
                'scanned_by_email' => $scannerEmail,
                'checked_in_at' => now(),
            ]);

            // Bump popularity score for event participation (checkin)
            if ($ticketSale->user) {
                app(PopularityScoreService::class)->bump($ticketSale->user, 4);
            }
        }

        // Get event fee settings for fee calculations
        $eventFeeSettings = null;
        if (\Illuminate\Support\Facades\Schema::hasColumn('event_fee_settings', 'link_up_event_id')) {
            $eventFeeSettings = EventFeeSetting::where('link_up_event_id', $ticketSale->link_up_event_id)->first();
        }
        if (!$eventFeeSettings) {
            $eventFeeSettings = EventFeeSetting::first();
        }

        // Calculate fees similar to BookingController
        $serviceFeeTotal = floatval($ticketSale->fee ?? 0);
        $processingFeeTotal = floatval($ticketSale->tax ?? 0);
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

        // If service/processing fees are zero on this row (because they were allocated to another row),
        // aggregate across all tickets in the same purchase (same stripe_id) to mirror eTicket view behavior.
        if (!empty($ticketSale->stripe_id)) {
            $siblingSales = TicketSale::where('stripe_id', $ticketSale->stripe_id)->get();
            if ($siblingSales->count() > 0) {
                $aggService = $siblingSales->sum(function ($t) {
                    return floatval($t->fee ?? 0);
                });
                $aggProcessing = $siblingSales->sum(function ($t) {
                    return floatval($t->tax ?? 0);
                });
                if ($serviceFeeTotal <= 0 && $aggService > 0) {
                    $serviceFeeTotal = $aggService;
                }
                if ($processingFeeTotal <= 0 && $aggProcessing > 0) {
                    $processingFeeTotal = $aggProcessing;
                }
            }
        }

        // Calculate totals
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
            $eventDate['end_date'] = $ticketSale?->event?->eventDetails?->recurr_end_date?->format('d F, Y');
            $eventDate['end_time'] = $ticketSale?->event?->eventDetails?->single_end_time?->format('H:i,A') ?? null;
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
            'cookout_total' => floatval($ticketSale->cookout_total ?? 0),
            // 'drinks_base' => $drinksBase,
            // 'bottles_base' => $bottlesBase,
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
            'cookout_addons' => $ticketSale->cookout_addons ?? [],
            'cookout_included_protein' => $ticketSale->cookout_included_protein,
            'cookout_included_sides' => $ticketSale->cookout_included_sides ?? [],
            'package_data' => $ticketSale->package_data,

            // Fee breakdown details removed (already fetched elsewhere)

            // Check-in status
            'is_already_scanned' => $isAlreadyScanned,
            'checked_in_at' => $existingCheckin?->checked_in_at,
            'scanned_by' => [
                'email' => $existingCheckin?->scanned_by_email,
                'name' => $existingCheckin?->scanner?->first_name . " " . $existingCheckin?->scanner?->first_name
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => $isAlreadyScanned ? 'Ticket already scanned' : 'Ticket scanned successfully',
            'data' => $data,
        ], 200);
    }
    /**
     * Create a new scanner
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:scan_sign_users,email',
            'password' => 'required|string|min:6|confirmed',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $organizerId = OrganizerProfile::where('user_id', $user->id)->value('id');

        $data = [
            'org_id' => $organizerId->id,
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'address' => $request->address,
            'status' => true, // Active by default
        ];

        // Handle image upload if provided
        if ($request->hasFile('scanner_image_object')) {
            $image = $request->file('scanner_image_object');
            $path = $image->store('scanners', 'public');
            $data['scanner_image_object'] = $path;
        }

        $scanner = ScanSignUser::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Scanner created successfully',
            'scanner' => $scanner
        ], 201);
    }

    /**
     * Update a scanner
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'error' => 'Scanner not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:scan_sign_users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'address' => $request->address,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle image upload if provided
        if ($request->hasFile('scanner_image_object')) {
            $image = $request->file('scanner_image_object');
            $path = $image->store('scanners', 'public');
            $data['scanner_image_object'] = $path;
        }

        $scanner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Scanner updated successfully',
            'scanner' => $scanner->fresh()
        ]);
    }

    /**
     * Delete a scanner
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'error' => 'Scanner not found'
            ], 404);
        }

        $scanner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scanner deleted successfully'
        ]);
    }

    /**
     * Toggle scanner status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();

        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'error' => 'Scanner not found'
            ], 404);
        }

        // Toggle status: 1 becomes 0, 0 becomes 1
        $scanner->status = $scanner->status == 1 ? 0 : 1;
        $scanner->save();

        return response()->json([
            'success' => true,
            'message' => 'Scanner status updated successfully',
            'scanner' => $scanner
        ]);
    }

    /**
     * Assign scanner role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        $user = Auth::user();
        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:scanner',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $scanner->syncRoles([$request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Scanner Role Assigned successfully'
        ]);
    }

    /**
     * Remove scanner role and permissions from a user.
     */
    public function removeRole($id)
    {
        $user = Auth::user();
        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner not found'
            ], 404);
        }

        $scanner->syncRoles([]);
        $scanner->syncPermissions([]);

        return response()->json([
            'success' => true,
            'message' => 'Scanner Role and Permissions removed successfully'
        ]);
    }

    /**
     * Get permissions for a scanner.
     */
    public function getPermissions($id)
    {
        $user = Auth::user();
        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner not found'
            ], 404);
        }

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
    public function assignPermissions(Request $request, $id)
    {
        $user = Auth::user();
        $scanner = ScanSignUser::where('user_id', $user->id)->find($id);

        if (!$scanner) {
            return response()->json([
                'success' => false,
                'message' => 'Scanner not found'
            ], 404);
        }

        if (!$scanner->hasRole('scanner')) {
            return response()->json([
                'success' => false,
                'message' => 'You must assign the "Scanner" role to this user before assigning individual permissions.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $scanner->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Scanner Permissions Assigned successfully'
        ]);
    }
}
