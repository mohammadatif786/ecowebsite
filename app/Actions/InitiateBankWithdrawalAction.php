<?php

namespace App\Actions;

use App\DTOs\BankWithdrawalDTO;
use App\Models\BankWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Balance;

class InitiateBankWithdrawalAction
{
    public function execute(BankWithdrawalDTO $dto): BankWithdrawal
    {
        $this->enforceRateLimit($dto->userId);
        $this->validateAmount($dto->amount);
        $this->validateBankSelection($dto);

        $feePercent = self::feePercent();
        $feeAmount = round($dto->amount * ($feePercent / 100), 2);
        $payoutAmount = max(round($dto->amount - $feeAmount, 2), 0);

        $user = User::findOrFail($dto->userId);
        
        // Check if user has sufficient balance
        $balance = $user->balance('USD')->value->get();
        if ($balance < $dto->amount) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient balance for this withdrawal.'],
            ]);
        }

        // Get the user's bank details to retrieve the actual bank info
        // A specific saved account (wallet hub) or, for older screens, its position in the list.
        $bankAccounts = \App\Models\UserBankAccount::where('user_id', $dto->userId)->orderBy('id')->get()
            ->filter(fn ($account) => ! empty($account->bank_name) && ! empty($account->account_number))
            ->values();

        $selectedBank = $dto->bankAccountId !== null
            ? $bankAccounts->firstWhere('id', $dto->bankAccountId)
            : ($bankAccounts[$dto->bankIndex] ?? null);

        if (! $selectedBank) {
            throw ValidationException::withMessages([
                'bank' => ['Invalid bank selection.'],
            ]);
        }

        $withdrawal = DB::transaction(function () use ($dto, $user, $selectedBank, $feePercent, $feeAmount, $payoutAmount) {
            // Re-check under a row lock so two quick withdrawals cannot both pass the balance check.
            User::whereKey($user->id)->lockForUpdate()->first();
            if ($user->balance('USD')->value->get() < $dto->amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Insufficient balance for this withdrawal.'],
                ]);
            }

            // Deduct amount from wallet by transferring to system custodian
            $custodian = \O21\LaravelWallet\Models\Custodian::of('system');
            
            transfer($dto->amount, 'USD')
                ->from($user)
                ->to($custodian)
                ->meta([
                    'type' => 'bank_withdrawal',
                    'withdrawal_id' => 'pending',
                    'bank_name' => $selectedBank->bank_name,
                    'fee_percent' => $feePercent,
                    'fee_amount' => $feeAmount,
                    'payout_amount' => $payoutAmount,
                    'note' => 'Bank withdrawal pending processing',
                ])
                ->commit();

            // Create withdrawal record with encrypted bank details
            return BankWithdrawal::create([
                'user_id' => $dto->userId,
                'amount' => $dto->amount,
                'fee_percent' => $feePercent,
                'fee_amount' => $feeAmount,
                'payout_amount' => $payoutAmount,
                'bank_name' => $selectedBank->bank_name,
                'account_number' => $selectedBank->account_number,
                'status' => 'pending',
            ]);
        });

        return $withdrawal;
    }

    private function enforceRateLimit(int $userId): void
    {
        $key = "bank_withdrawal:initiate:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many withdrawal attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 300); // 5 minutes
    }

    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Withdrawal amount must be greater than 0.'],
            ]);
        }

        if ($amount < 10) {
            throw ValidationException::withMessages([
                'amount' => ['Minimum withdrawal amount is $10.'],
            ]);
        }

        if ($amount > 10000) {
            throw ValidationException::withMessages([
                'amount' => ['Maximum withdrawal amount is $10,000.'],
            ]);
        }
    }

    private function validateBankSelection(BankWithdrawalDTO $dto): void
    {
        if ($dto->bankAccountId === null && $dto->bankIndex < 0) {
            throw ValidationException::withMessages([
                'bank' => ['Please select a bank account.'],
            ]);
        }
    }

    public static function feePercent(): float
    {
        $feePercentRaw = env('BANK_PROCESSING_FEE_PERCENT', 5);
        $feePercent = is_numeric($feePercentRaw) ? (float) $feePercentRaw : 5.0;

        if (!is_finite($feePercent) || $feePercent < 0) {
            $feePercent = 5.0;
        }

        return $feePercent;
    }
}
