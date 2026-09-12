<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerCashOutRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

class SellerCashOutController extends Controller
{
    public function index()
    {
        $requests = SellerCashOutRequest::with(['user', 'bankAccount'])
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'user_name' => $request->user->name ?? 'Unknown',
                    'user_email' => $request->user->email ?? 'Unknown',
                    'amount' => $request->amount,
                    'status' => $request->status,
                    'rejection_reason' => $request->rejection_reason,
                    'created_at' => $request->created_at->toISOString(),
                    'processed_at' => $request->processed_at?->toISOString(),
                    'bank_account' => $request->bankAccount ? [
                        'bank_name' => $request->bankAccount->bank_name,
                        'account_number' => substr($request->bankAccount->account_number, -4),
                    ] : null,
                ];
            });

        return inertia('admin/SellerCashOut/Index', [
            'requests' => $requests,
        ]);
    }

    public function approve($id)
    {
        $admin = auth()->user();
        $this->verifyAdminRole($admin);
        $this->enforceRateLimit($admin->id, 'approve');

        $request = SellerCashOutRequest::with('user')->lockForUpdate()->findOrFail($id);

        // Only pending requests can be approved
        if ($request->status !== 'pending') {
            Log::warning('Attempted to approve non-pending seller cash out request', [
                'admin_id' => $admin->id,
                'request_id' => $id,
                'status' => $request->status,
            ]);
            throw ValidationException::withMessages([
                'status' => ['This request cannot be approved. It may already be processed.'],
            ]);
        }

        $user = $request->user;

        DB::transaction(function () use ($request, $user, $admin) {
            // Update request status to completed
            $request->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Note: Funds are already transferred to seller's wallet when they request the cash-out
            // Admin approval is just a formality to mark it as processed

            // Log audit trail
            Log::info('Seller cash out request approved by admin', [
                'admin_id' => $admin->id,
                'request_id' => $request->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
            ]);
        });

        return back()->with('success', 'Cash out request approved and funds transferred successfully.');
    }

    public function reject(Request $request, $id)
    {
        $admin = auth()->user();
        $this->verifyAdminRole($admin);
        $this->enforceRateLimit($admin->id, 'reject');

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $cashOutRequest = SellerCashOutRequest::with('user')->lockForUpdate()->findOrFail($id);

        // Only pending requests can be rejected
        if ($cashOutRequest->status !== 'pending') {
            Log::warning('Attempted to reject non-pending seller cash out request', [
                'admin_id' => $admin->id,
                'request_id' => $id,
                'status' => $cashOutRequest->status,
            ]);
            throw ValidationException::withMessages([
                'status' => ['This request cannot be rejected. It may already be processed.'],
            ]);
        }

        DB::transaction(function () use ($cashOutRequest, $request, $admin) {
            // Update request status to rejected
            $cashOutRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason ?? 'Rejected by admin',
                'processed_at' => now(),
            ]);

            // Log audit trail
            Log::info('Seller cash out request rejected by admin', [
                'admin_id' => $admin->id,
                'request_id' => $cashOutRequest->id,
                'user_id' => $cashOutRequest->user_id,
                'amount' => $cashOutRequest->amount,
                'reason' => $request->reason ?? 'Rejected by admin',
            ]);
        });

        return back()->with('success', 'Cash out request rejected successfully.');
    }

    private function enforceRateLimit(int $adminId, string $action): void
    {
        $key = "admin:seller_cash_out:{$action}:{$adminId}";

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Admin seller cash out action rate limit exceeded', [
                'admin_id' => $adminId,
                'action' => $action,
                'available_in' => $seconds,
            ]);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many {$action} attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 60); // 1 minute
    }

    private function verifyAdminRole($user): void
    {
        if (!$user || !$user->hasRole('admin')) {
            Log::alert('Unauthorized attempt to access admin seller cash out action', [
                'user_id' => $user->id ?? null,
                'ip' => request()->ip(),
            ]);
            abort(403, 'Unauthorized. Admin access required.');
        }
    }
}
