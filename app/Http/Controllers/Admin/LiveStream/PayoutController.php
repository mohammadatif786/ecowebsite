<?php

namespace App\Http\Controllers\Admin\LiveStream;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\OrganizerProfile;
use App\Models\OrganizerContact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Mail\PayoutApprovalMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = $request->only(['search', 'status', 'method', 'event_id']);

        // Build query with filters
        $query = Payout::with(['event', 'organizer','organizer.bankAccounts'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                          ->orWhere('author', 'like', "%{$search}%")
                          ->orWhereHas('event', function ($q) use ($search) {
                              $q->where('title', 'like', "%{$search}%");
                          });
                });
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['method'] ?? null, function ($q, $method) {
                $q->where('method', $method);
            })
            ->when($filters['event_id'] ?? null, function ($q, $eventId) {
                $q->where('event_id', $eventId);
            });

        // Get all payouts for frontend filtering and stats
        $payouts = $query->orderBy('created_at', 'desc')->get();

        // Format payouts for frontend display
        $formattedPayouts = $payouts->map(function ($payout) {
            return [
                'id' => $payout->id,
                'reference' => $payout->reference,
                'amount' => $payout->amount ?? 0,
                'fee_amount' => $payout->fee_amount ?? 0,
                'net_amount' => $payout->net_amount ?? $payout->amount,
                'method' => $payout->method,
                'status' => $payout->status,
                'notes' => $payout->notes,
                'created_at' => $payout->created_at,
                'updated_at' => $payout->updated_at,
                'author' => $payout->author,
                'organizer_id' => $payout->organizer_id,
                'event_id' => $payout->event_id,
                // Format event data for display
                'event' => $payout->event ? [
                    'id' => $payout->event->id,
                    'title' => $payout->event->title ?? '-',
                ] : null,
                // Format organizer data for display
                'organizer' => $payout->organizer ? [
                    'id' => $payout->organizer->id,
                    'name' => $payout->organizer->user->name ?? '-',
                    'bank' => $payout->organizer->bank ?? '-',
                ] : null,
                'organizer_bank' => $payout->organizer && $payout->organizer->bankAccounts->isNotEmpty() ? [
                    'name' => $payout->organizer->bankAccounts->first()->bank_name ?? '-',
                    'account_number' => $payout->organizer->bankAccounts->first()->account_number ?? '-',
                    'routing_number' => $payout->organizer->bankAccounts->first()->routing_number ?? '-',
                    'paypal_id' => $payout->organizer->bankAccounts->first()->paypal_id ?? '-',
                ] : null,
                // time line
                'timeline' => [
                [
                    'step' => 'Payout Requested',
                    'status' => 'pending',
                    'time' => $payout->created_at
                ],
                [
                    'step' => 'Moved to Processing',
                    'status' => 'ok',
                    'time' => $payout->updated_at
                ],
                [
                    'step' => 'Sent to Bank',
                    'status' => 'pending',
                    'time' => now()->toISOString()
                ]
            ]
            ];
        });

        // Calculate stats
        $stats = [
            'pending' => $payouts->where('status', 'pending')->count(),
            'processing' => $payouts->where('status', 'processing')->count(),
            'completed' => $payouts->where('status', 'approved')->count(),
            'failed' => $payouts->where('status', 'rejected')->count(),
        ];

        // Get organizers and events for filter dropdowns
        $organizers = OrganizerProfile::select('id', 'user_id')->with('user:id,name')->get();
        $events = \App\Models\LinkUpEvent::select('id', 'title')->get();

        return Inertia::render('admin/liveStream/payouts/Index', [
            'payouts' => $formattedPayouts,
            'stats' => $stats,
            'organizers' => $organizers,
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    /**
     * Approve a payout request
     */
    public function approve(Request $request, Payout $payout)
    {
        $payout->update([
            'status' => 'approved',
            'notes' => $request->notes ?? 'Approved by admin'
        ]);

        // Send email to organizer
        $organizerContact = OrganizerContact::where('organizer_id', $payout->organizer_id)->first();

        if ($organizerContact && $organizerContact->email) {
            try {
                Mail::to($organizerContact->email)->send(new PayoutApprovalMail($payout, $organizerContact));
            } catch (\Exception $e) {
                // Log the error but don't fail the approval
                Log::error('Failed to send payout approval email: ' . $e->getMessage());
            }
        }

        return back()->with('message', 'Payout approved successfully');
    }

    /**
     * Reject a payout request
     */
    public function reject(Request $request, Payout $payout)
    {
        $payout->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? 'Rejected by admin'
        ]);

        return back()->with('message', 'Payout rejected');
    }

    /**
     * Move payout to processing
     */
    public function moveToProcessing(Payout $payout)
    {
        $payout->update([
            'status' => 'processing'
        ]);

        return back()->with('message', 'Payout moved to processing');
    }

    /**
     * Retry the process of payout
     */
    public function retry(Payout $payout) {
        $payout->update([
            'status' => 'pending'
        ]);

        return back()->with('message', 'Payout moved to pending');
    }
}
