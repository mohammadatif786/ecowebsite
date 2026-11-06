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

        // Locked, status-checked and refunded inside one transaction (no double refund with an admin reject).
        return app(\App\Domain\Wallet\Actions\SettleBankWithdrawalAction::class)->cancelByMember($withdrawalId, $userId, function (BankWithdrawal $withdrawal) {
            // Fraud prevention: only within 30 minutes of creation
            if ($withdrawal->created_at->lt(now()->subMinutes(30))) {
                throw ValidationException::withMessages([
                    'time' => ['This withdrawal can no longer be cancelled. Cancellation is only allowed within 30 minutes of submission.'],
                ]);
            }
        });
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
