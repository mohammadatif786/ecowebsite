<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizerUserProfileRequest;
use App\Models\EventFeeSetting;
use App\Models\LinkUpEvent;
use App\Models\OrganizerBankAccount;
use App\Models\OrganizerProfile;
use App\Models\Payout;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function request(Request $request)
    {
        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();
        $settings = Settings::whereIn('key', ['paypal', 'stripe', 'bank'])->get();
        $bankAccounts = OrganizerBankAccount::where('organizer_id', $organizer->id)->get();
        $payout_processing = EventFeeSetting::value('wire_processing_fee_pct');

        $filters = $request->only(['reference', 'event_id', 'method', 'status']);

        $events = LinkUpEvent::with(['eventDetails', 'ticketSales'])
            ->where('organizer_id', $organizer->id)
            ->get()
            ->map(function ($event) {
                $event->net_sales = $event->ticketSales->sum(function ($ticket) {
                    $subTotal = (float) ($ticket->sub_total ?? 0);
                    $drinksTotal = (float) ($ticket->drinks_total ?? 0);
                    $tablesTotal = (float) ($ticket->tables_total ?? 0);
                    $discount = (float) ($ticket->coupan_amount ?? 0);
                    $net = $subTotal + $drinksTotal + $tablesTotal - $discount;
                    return $net;
                });

                $event->paid_out = Payout::where('event_id', $event->id)
                    ->where('status', 'verified')
                    ->sum('amount');

                $event->available_balance = $event->net_sales - $event->paid_out;

                $latestPayout = Payout::where('event_id', $event->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $event->payout_status = $latestPayout ? $latestPayout->status : 'no_request';

                return $event;
            });

        $payoutsQuery = Payout::with(['event.eventDetails', 'event.ticketSales'])
            ->where('organizer_id', $organizer->id);

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

        return Inertia::render('organizer/payout/Request', [
            'events' => $events,
            'payouts' => $payouts,
            'settings' => $settings,
            'bankAccounts' => $bankAccounts,
            'filters' => $filters,
            'payout_processing' => $payout_processing
        ]);
    }


    public function method()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $paypal = Settings::where('key', 'paypal')->first();
        $stripe = Settings::where('key', 'stripe')->first();
        $bankAccounts = OrganizerBankAccount::where('organizer_id', $organizer->id)->get();
        return Inertia::render('organizer/payout/Method', [
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

        return redirect()->back()->with('success', 'PayPal info updated successfully.');
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

        return redirect()->back()->with('success', 'Stripe info updated successfully.');
    }

    public function storeBank(OrganizerUserProfileRequest $request)
    {
        $validate_data = $request->validated();
        $organizer = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );
        $accountIds = collect($validate_data['banks'])->pluck('id')->filter()->toArray();

        OrganizerBankAccount::where('organizer_id', $organizer->id)
            ->whereNotIn('id', $accountIds)
            ->delete();

        foreach ($validate_data['banks'] as $bank) {
            $profile = OrganizerBankAccount::updateOrCreate(
                [
                    'id'           => $bank['id'] ?? null,
                    'organizer_id' => $organizer->id,
                ],
                [
                    'bank_name'      => $bank['bank_name'],
                    'routing_number' => $bank['routing_number'],
                    'account_number' => $bank['account_number'],
                    'paypal_id'      => $validate_data['paypal_id'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Bank accounts updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required',
            'method' => 'required',
            'amount' => 'required',
            'destination' => 'required',
        ]);

        $user = Auth::user();
        $organizer = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizer) {
            return redirect()->back()->with('error', 'Organizer profile not found.');
        }

        // Check if the event belongs to this organizer
        $event = LinkUpEvent::where('id', $validated['event_id'])
            ->where('organizer_id', $organizer->id)
            ->first();

        if (!$event) {
            return redirect()->back()->with('error', 'Event processing fee not found.');
        }
        // Check if the processing fee is available
        $payout_processing = EventFeeSetting::value('wire_processing_fee_pct');
        if (!$payout_processing) {
            return redirect()->back()->with('error', 'Event processing fee not found.');
        }

        // Generate unique reference ID
        $referenceId = 'PO_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Calculate fee and net amount
        $feeAmount = $validated['amount'] * (float)($payout_processing / 100);
        $netAmount = $validated['amount'] - $feeAmount;

        // Create payout record
        $authUser = Auth::user();
        $payout = Payout::create([
            'amount' => $validated['amount'],
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'currency' => 'USD', // Default currency
            'method' => $validated['method'],
            'author' => $authUser->name,
            'destination' => $validated['destination'],
            'status' => 'pending',
            'reference' => $referenceId,
            'firebase_id' => $referenceId,
            'event_id' => $validated['event_id'],
            'organizer_id' => $organizer->id,
        ]);

        return redirect()->back()->with('success', 'Payout request submitted successfully. Reference: ' . $referenceId);
    }

    /**
     * Verify transfer
     */
    public function verifyTransfer($transfer_id) {
        $payout = Payout::with('event','organizer')->where('reference', $transfer_id)->first();
        return Inertia::render('organizer/payout/Component/VerifyTransfer', [
            'payout' => $payout
        ]);
    }

    /**
     * verified payout by organizer
     */
    public function verifiedTransfer($payout_id) {
        $payout = Payout::with('event','organizer')->where('id', $payout_id)->first();
        $payout->update([
            'status' => 'verified'
        ]);
        return redirect()->back()->with('success', 'Payout verified successfully.');
    }

    /**
     * Download payout PDF
     */
    public function downloadPayoutPdf($payout_id) {
        $payout = Payout::with('event','organizer')->findOrFail($payout_id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('organizer.payout.pdf', compact('payout'));
        return $pdf->download('payout_' . $payout->reference . '.pdf');
    }
}
