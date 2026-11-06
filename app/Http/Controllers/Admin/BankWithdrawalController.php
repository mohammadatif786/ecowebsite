<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UserBankAccountHelper;
use App\Http\Controllers\Controller;
use App\Jobs\BankWithDrawalJob;
use App\Models\BankWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

class BankWithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = BankWithdrawal::with('user')
            ->latest()
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'user_id' => $withdrawal->user_id,
                    'user_name' => $withdrawal->user->name ?? 'Unknown',
                    'user_email' => $withdrawal->user->email ?? 'Unknown',
                    'amount' => $withdrawal->amount,
                    'fee_percent' => $withdrawal->fee_percent,
                    'fee_amount' => $withdrawal->fee_amount,
                    'payout_amount' => $withdrawal->payout_amount,
                    'bank_name' =>  UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->bank_name),
                    'account_number' =>  UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->account_number),
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toISOString(),
                    'processed_at' => $withdrawal->processed_at?->toISOString(),
                    'failure_reason' => $withdrawal->failure_reason,
                ];
            });

        return inertia('admin/BankWithdrawals/Index', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function approve($id, \App\Domain\Wallet\Actions\SettleBankWithdrawalAction $settle)
    {
        $admin = Auth::user();
        $this->verifyAdminRole($admin);
        $this->enforceRateLimit($admin->id, 'approve');

        $settle->approve((int) $id, (int) $admin->id); // locked + status-checked inside one transaction

        return back()->with('success', 'Withdrawal approved successfully.');
    }

    public function reject(Request $request, $id, \App\Domain\Wallet\Actions\SettleBankWithdrawalAction $settle)
    {
        $admin = Auth::user();
        $this->verifyAdminRole($admin);
        $this->enforceRateLimit($admin->id, 'reject');

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $settle->reject((int) $id, (int) $admin->id, $request->reason);

        return back()->with('success', 'Withdrawal rejected and amount refunded to user.');
    }

    private function enforceRateLimit(int $adminId, string $action): void
    {
        $key = "admin:bank_withdrawal:{$action}:{$adminId}";

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many {$action} attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 60);
    }

    private function mailData(BankWithdrawal $withdrawal): array
    {
        return [
            'user_name' => $withdrawal->user->name ?? 'User',
            'user_email' => $withdrawal->user->email ?? 'Unknown',
            'total_amount' => $withdrawal->amount,
            'fee_amount' => $withdrawal->fee_amount,
            'payout_amount' => $withdrawal->payout_amount,
            'bank_name' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->bank_name) ?? 'Unknown Bank',
            'account_number' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->account_number) ?? 'Unknown Account',
            'note' => $withdrawal->status === 'completed'
                ? 'Your bank withdrawal has been approved and is being processed.'
                : 'Your bank withdrawal has been rejected.' . ($withdrawal->failure_reason ? " Reason: {$withdrawal->failure_reason}" : ''),
        ];
    }

    private function verifyAdminRole($user): void
    {
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }
}
