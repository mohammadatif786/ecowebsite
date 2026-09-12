<?php

namespace App\Actions;

use App\Models\BankWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

class CancelBankWithdrawalAction
{
    public function execute(int $withdrawalId, int $userId): BankWithdrawal
    {
        $this->enforceRateLimit($userId);

        $withdrawal = BankWithdrawal::where('id', $withdrawalId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();

        // Only allow cancellation if status is pending
        if ($withdrawal->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['This withdrawal cannot be cancelled. It may already be processed.'],
            ]);
        }

        // Fraud prevention: Only allow cancellation within 30 minutes of creation
        $createdAt = $withdrawal->created_at;
        $cutoff = now()->subMinutes(30);
        if ($createdAt->lt($cutoff)) {
            throw ValidationException::withMessages([
                'time' => ['This withdrawal can no longer be cancelled. Cancellation is only allowed within 30 minutes of submission.'],
            ]);
        }

        $user = User::findOrFail($userId);
        $systemCustodian = Custodian::of('system');

        DB::transaction(function () use ($withdrawal, $user, $systemCustodian) {
            // Refund the amount back to the user from system custodian
            transfer($withdrawal->amount, 'USD')
                ->from($systemCustodian)
                ->to($user)
                ->meta([
                    'type' => 'bank_withdrawal_refund',
                    'withdrawal_id' => $withdrawal->id,
                    'note' => 'Withdrawal cancelled - amount refunded',
                ])
                ->commit();

            // Update withdrawal status to cancelled
            $withdrawal->update([
                'status' => 'cancelled',
                'failure_reason' => 'Cancelled by user',
                'processed_at' => now(),
            ]);
        });

        return $withdrawal->refresh();
    }

    private function enforceRateLimit(int $userId): void
    {
        $key = "bank_withdrawal:cancel:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many cancellation attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 300); // 5 minutes
    }
}
