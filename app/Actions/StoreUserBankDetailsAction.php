<?php

namespace App\Actions;

use App\DTOs\UserBankDetailsDTO;
use App\Models\UserBankAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class StoreUserBankDetailsAction
{
    public function execute(UserBankDetailsDTO $dto): void
    {
        $this->enforceRateLimit($dto->userId);

        $paypalId = $dto->paypalId !== null ? trim((string) $dto->paypalId) : null;

        $banks = collect($dto->banks)
            ->map(function ($b) {
                $b = is_array($b) ? $b : [];

                $bankName = $b['bank_name'] ?? $b['name'] ?? null;
                $accountNumber = $b['account_number'] ?? $b['accountNumber'] ?? null;
                $routingNumber = $b['routing_number'] ?? $b['routingNumber'] ?? null;
                $accountType = $b['account_type'] ?? $b['accountType'] ?? null;
                $isDefault = $b['is_default'] ?? $b['isDefault'] ?? false;

                return [
                    'bank_name' => $bankName !== null ? trim((string) $bankName) : null,
                    'account_number' => $accountNumber !== null ? preg_replace('/\s+/', '', (string) $accountNumber) : null,
                    'routing_number' => $routingNumber !== null ? preg_replace('/\s+/', '', (string) $routingNumber) : null,
                    'account_type' => $accountType !== null ? trim((string) $accountType) : null,
                    'is_default' => filter_var($isDefault, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(function ($b) {
                return !empty($b['bank_name']) || !empty($b['account_number']) || !empty($b['routing_number']);
            })
            ->values();

        if ($paypalId === null && $banks->isEmpty()) {
            throw ValidationException::withMessages([
                'banks' => ['Please provide at least one bank entry or a PayPal ID.'],
            ]);
        }

        if ($banks->count() > 10) {
            throw ValidationException::withMessages([
                'banks' => ['You can add a maximum of 10 bank accounts.'],
            ]);
        }

        $this->validateBankFormats($banks);

        DB::transaction(function () use ($dto, $paypalId, $banks) {
            UserBankAccount::where('user_id', $dto->userId)->delete();

            if ($banks->isEmpty()) {
                UserBankAccount::create([
                    'user_id' => $dto->userId,
                    'paypal_id' => $paypalId,
                ]);

                return;
            }

            // Ensure only one bank is marked as default
            $hasDefault = $banks->contains('is_default', true);
            
            foreach ($banks as $index => $bank) {
                // If no bank is explicitly marked as default, mark the first one as default
                $isDefault = $bank['is_default'] ?? false;
                if (!$hasDefault && $index === 0) {
                    $isDefault = true;
                }

                UserBankAccount::create([
                    'user_id' => $dto->userId,
                    'paypal_id' => $paypalId,
                    'bank_name' => $bank['bank_name'] ?? null,
                    'account_number' => $bank['account_number'] ?? null,
                    'routing_number' => $bank['routing_number'] ?? null,
                    'account_type' => $bank['account_type'] ?? null,
                    'is_default' => $isDefault,
                ]);
            }
        });
    }

    private function enforceRateLimit(int $userId): void
    {
        $key = "bank_details:update:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'rate_limit' => ["Too many attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }

        RateLimiter::hit($key, 60);
    }

    private function validateBankFormats(Collection $banks): void
    {
        foreach ($banks as $bank) {
            if (!empty($bank['account_type']) && !in_array($bank['account_type'], ['checking', 'savings'], true)) {
                throw ValidationException::withMessages([
                    'banks' => ['Invalid account type.'],
                ]);
            }

            if (!empty($bank['routing_number']) && !preg_match('/^[0-9]{5,20}$/', (string) $bank['routing_number'])) {
                throw ValidationException::withMessages([
                    'banks' => ['Invalid routing number format.'],
                ]);
            }

            if (!empty($bank['account_number']) && !preg_match('/^[0-9A-Za-z]{4,34}$/', (string) $bank['account_number'])) {
                throw ValidationException::withMessages([
                    'banks' => ['Invalid account number format.'],
                ]);
            }
        }
    }
}
