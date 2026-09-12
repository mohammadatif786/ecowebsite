<?php

namespace App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard;

use App\Http\Controllers\Controller;
use App\Models\SellerCashOutRequest;
use App\Services\SellerEarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

class SellerCashOutController extends Controller
{
    public function __construct(
        private SellerEarningService $earningService
    ) {}

    /**
     * Create a new cash out request
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $this->enforceRateLimit($userId, 'create');

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10|max:10000',
            // 'bank_account_id' => 'required|exists:user_bank_accounts,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $amount = (float) $request->amount;
        // $bankAccountId = $request->bank_account_id;

        // // Verify the bank account belongs to the user
        // $bankAccount = \App\Models\UserBankAccount::where('id', $bankAccountId)
        //     ->where('user_id', $userId)
        //     ->first();

        // if (!$bankAccount) {
        //     throw ValidationException::withMessages([
        //         'bank_account_id' => ['Invalid bank account selected.'],
        //     ]);
        // }

        // Check if user has sufficient earnings
        $availableEarnings = $this->earningService->getAvailableEarnings($userId);

        if ($availableEarnings < $amount) {
            Log::warning('Insufficient earnings for cash out', [
                'user_id' => $userId,
                'requested_amount' => $amount,
                'available_earnings' => $availableEarnings,
            ]);
            throw ValidationException::withMessages([
                'amount' => ['Insufficient earnings. Your available earnings are $' . number_format($availableEarnings, 2)],
            ]);
        }

        DB::transaction(function () use ($userId, $amount) {
            $user = \App\Models\User::find($userId);
            
            // Add amount to user's wallet balance using wallet package transaction
            $wallet = \O21\LaravelWallet\Models\Balance::firstOrCreate(
                [
                    'payable_id' => $userId,
                    'payable_type' => get_class($user),
                    'currency' => 'USD',
                ],
                [
                    'value' => 0,
                    'value_pending' => 0,
                    'value_on_hold' => 0,
                ]
            );
            
            // Use the wallet's internal method to add value
            $wallet->value = $wallet->value->add($amount);
            $wallet->save();
            
            // Create cash out request with completed status
            SellerCashOutRequest::create([
                'user_id' => $userId,
                'bank_account_id' => null,
                'amount' => $amount,
                'status' => 'completed',
                'processed_at' => now(),
            ]);
            
            // Log the transaction
            Log::info('Seller cash out added to wallet and request marked as completed', [
                'user_id' => $userId,
                'amount' => $amount,
            ]);
        });

        return back()->with('success', 'Cash out added to your wallet balance successfully.');
    }

    /**
     * Cancel a pending cash out request
     */
    public function cancel($id)
    {
        $userId = Auth::id();
        $this->enforceRateLimit($userId, 'cancel');

        $request = SellerCashOutRequest::where('id', $id)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();

        // Only pending requests can be cancelled
        if ($request->status !== 'pending') {
            Log::warning('Attempted to cancel non-pending cash out request', [
                'user_id' => $userId,
                'request_id' => $id,
                'status' => $request->status,
            ]);
            throw ValidationException::withMessages([
                'status' => ['This request cannot be cancelled. It may already be processed.'],
            ]);
        }

        // Time limit: can cancel within 30 minutes of creation
        $minutesSinceCreation = now()->diffInMinutes($request->created_at);
        if ($minutesSinceCreation > 30) {
            Log::warning('Attempted to cancel cash out request after time limit', [
                'user_id' => $userId,
                'request_id' => $id,
                'minutes_since_creation' => $minutesSinceCreation,
            ]);
            throw ValidationException::withMessages([
                'time_limit' => ['Cash out requests can only be cancelled within 30 minutes of submission.'],
            ]);
        }

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'cancelled',
                'processed_at' => now(),
            ]);

            Log::info('Seller cash out request cancelled - amount refunded to available earnings', [
                'user_id' => $request->user_id,
                'request_id' => $request->id,
                'amount' => $request->amount,
            ]);
        });

        return back()->with('success', 'Cash out request cancelled successfully. Amount refunded to available earnings.');
    }

    /**
     * Get cash out requests for the seller
     */
    public function index()
    {
        $userId = Auth::id();
        $requests = SellerCashOutRequest::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'amount' => $request->amount,
                    'status' => $request->status,
                    'rejection_reason' => $request->rejection_reason,
                    'created_at' => $request->created_at->toISOString(),
                    'processed_at' => $request->processed_at?->toISOString(),
                ];
            });

        return response()->json([
            'requests' => $requests,
        ]);
    }

    /**
     * Get available and pending earnings for cash out
     */
    public function getAvailable()
    {
        $userId = Auth::id();
        $available = $this->earningService->getAvailableEarnings($userId);
        $pending = $this->earningService->getPendingEarnings($userId);

        return response()->json([
            'available' => $available,
            'pending' => $pending,
        ]);
    }

    /**
     * Enforce rate limiting to prevent abuse
     */
    private function enforceRateLimit(int $userId, string $action): void
    {
        $key = "seller:cash_out:{$action}:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Seller cash out action rate limit exceeded', [
                'user_id' => $userId,
                'action' => $action,
                'available_in' => $seconds,
            ]);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many {$action} attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 300); // 5 minutes
    }
}
