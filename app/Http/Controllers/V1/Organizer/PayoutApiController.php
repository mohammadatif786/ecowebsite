<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventFeeSetting;
use App\Models\LinkUpEvent;
use App\Models\OrganizerBankAccount;
use App\Models\OrganizerProfile;
use App\Models\Organize;
use App\Models\Payout;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutApiController extends Controller
{
    public function request(Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        $settings = Settings::whereIn('key', ['paypal', 'stripe', 'bank'])->get();
        $bankAccounts = OrganizerBankAccount::where('organizer_id', $organizer->id)->get();
        $payout_processing = EventFeeSetting::value('wire_processing_fee_pct');

        // Get filter parameters
        $filters = $request->only(['reference', 'event_id', 'method', 'status']);

        // Fetch ALL events for "My Events" tab with calculations
        $events = LinkUpEvent::with(['eventDetails', 'ticketSales'])
            ->where('organizer_id', $organizer->id)
            ->get()
            ->map(function ($event) {
                // Calculate total net sales from all ticket sales (exclude fee and tax)
                // Include only: ticket sub_total + drinks_total + tables_total - discount
                $event->net_sales = $event->ticketSales->sum(function ($ticket) {
                    $subTotal = (float) ($ticket->sub_total ?? 0);
                    $drinksTotal = (float) ($ticket->drinks_total ?? 0);
                    $tablesTotal = (float) ($ticket->tables_total ?? 0);
                    $discount = (float) ($ticket->coupan_amount ?? 0);
                    $net = $subTotal + $drinksTotal + $tablesTotal - $discount;
                    return $net;
                });

                // Calculate total already paid out (verified payouts only)
                $event->paid_out = Payout::where('event_id', $event->id)
                    ->where('status', 'verified')
                    ->sum('amount');

                // Calculate available balance
                $event->available_balance = $event->net_sales - $event->paid_out;

                // Get latest payout status for this event
                $latestPayout = Payout::where('event_id', $event->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $event->payout_status = $latestPayout ? $latestPayout->status : 'no_request';

                return $event;
            });

        // Fetch payouts with related events and apply filters for "Payout Reports" tab
        $payoutsQuery = Payout::with(['event.eventDetails', 'event.ticketSales'])
            ->where('organizer_id', $organizer->id);

        // Apply filters
        if (!empty($filters['reference'])) {
            $payoutsQuery->where('reference', 'like', '%' . $filters['reference'] . '%');
        }

        if (!empty($filters['event_id'])) {
            $payoutsQuery->where('event_id', $filters['event_id']);
        }

        if (!empty($filters['method'])) {
            $payoutsQuery->where('method', $filters['method']);
        }

        if (!empty($filters['status'])) {
            $payoutsQuery->where('status', $filters['status']);
        }

        $payouts = $payoutsQuery->get();

        return response()->json([
            'events' => $events, // All events with calculations for "My Events" tab
            'payouts' => $payouts, // Payout requests for "Payout Reports" tab
            'settings' => $settings,
            'bankAccounts' => $bankAccounts,
            'filters' => $filters, // Pass current filters back to frontend
            'payout_processing' => $payout_processing
        ]);
    }


    public function method()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $paypal = Settings::where('key', 'paypal')->first();
        $stripe = Settings::where('key', 'stripe')->first();
        $bankAccounts = OrganizerBankAccount::where('organizer_id', $organizer->id)->get();
        return response()->json([
            'paypal' => $paypal,
            'stripe' => $stripe,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function storePayPal(Request $request)
    {
        $request->validate([
            'clientId' => 'nullable|string',
            'clientSecret' => 'nullable|string',
        ]);

        Settings::updateOrCreate(
            ['key' => 'paypal'],
            ['value' => json_encode([
                'clientId' => $request->clientId,
                'clientSecret' => $request->clientSecret,
            ])]
        );

        return response()->json(['success' => true, 'message' => 'PayPal info updated successfully.']);
    }

    public function storeStripe(Request $request)
    {
        $request->validate([
            'clientId' => 'nullable|string',
            'clientSecret' => 'nullable|string',
        ]);
        Settings::updateOrCreate(
            ['key' => 'stripe'],
            ['value' => json_encode([
                'clientId' => $request->clientId,
                'clientSecret' => $request->clientSecret,
            ])]
        );

        return response()->json(['success' => true, 'message' => 'Stripe info updated successfully.']);
    }

    public function storeBank(Request $request)
    {
        $request->validate([
            'bankAccounts' => 'required|array',
            'bankAccounts.*.bankName' => 'required|string|max:255',
            'bankAccounts.*.accountNumber' => 'required|string|max:255',
            'bankAccounts.*.routingNumber' => 'required|string|max:255',
        ]);

        $organizer = OrganizerProfile::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        // Delete old accounts (optional, depending on your needs)
        OrganizerBankAccount::where('organizer_id', $organizer->id)->delete();

        foreach ($request->bankAccounts as $account) {
            OrganizerBankAccount::create([
                'organizer_id' => $organizer->id,
                'bank_name' => $account['bankName'],
                'account_number' => $account['accountNumber'],
                'routing_number' => $account['routingNumber'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Bank accounts updated successfully.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required',
            'method' => 'required',
            'amount' => 'required',
            'destination' => 'required',
        ]);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return response()->json(['success' => false, 'message' => 'Organizer profile not found.'], 404);
        }

        // Check if the event belongs to this organizer
        $event = LinkUpEvent::where('id', $request->event_id)
            ->where('organizer_id', $organizer->id)
            ->first();

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found or unauthorized.'], 404);
        }
        // Check if the processing fee available
        $payout_processing = EventFeeSetting::value('wire_processing_fee_pct');
        if (!$payout_processing) {
            return response()->json(['success' => false, 'message' => 'Event processing fee not found.'], 404);
        }

        // Generate unique reference ID
        $referenceId = 'PO_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Calculate fee and net amount
        $feeAmount = $request->amount * (float)($payout_processing / 100);
        $netAmount = $request->amount - $feeAmount;

        // Create payout record
        $payout = Payout::create([
            'amount' => $request->amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'currency' => 'USD', // Default currency
            'method' => $request->method,
            'author' => $user->name,
            'destination' => $request->destination,
            'status' => 'pending',
            'reference' => $referenceId,
            'firebase_id' => $referenceId,
            'event_id' => $request->event_id,
            'organizer_id' => $organizer->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Payout request submitted successfully. Reference: ' . $referenceId]);
    }
}
