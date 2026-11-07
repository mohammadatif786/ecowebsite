<?php

namespace App\Domain\Wallet\Actions;

use App\Models\UserBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Adds one bank account to the member's saved list (the older bank-details screen replaces the whole list).
 */
final class AddBankAccountAction
{
    public const MAX_ACCOUNTS = 10;

    /**
     * @param  array{bank_name: string, account_number: string, routing_number?: ?string, account_type?: ?string, is_default?: bool}  $data
     */
    public function execute(int $userId, array $data): UserBankAccount
    {
        $key = "bank_details:update:{$userId}";
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'bank_name' => ['Too many attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.'],
            ])->status(429);
        }
        RateLimiter::hit($key, 60);

        return DB::transaction(function () use ($userId, $data) {
            $existing = UserBankAccount::where('user_id', $userId)->lockForUpdate()->get()
                ->filter(fn ($a) => ! empty($a->bank_name) && ! empty($a->account_number));

            if ($existing->count() >= self::MAX_ACCOUNTS) {
                throw ValidationException::withMessages(['bank_name' => ['You can save up to ' . self::MAX_ACCOUNTS . ' bank accounts.']]);
            }

            $number = preg_replace('/\s+/', '', $data['account_number']);
            if ($existing->contains(fn ($a) => $a->account_number === $number && strcasecmp((string) $a->bank_name, $data['bank_name']) === 0)) {
                throw ValidationException::withMessages(['account_number' => ['This bank account is already saved.']]);
            }

            $makeDefault = (bool) ($data['is_default'] ?? false) || $existing->isEmpty();
            if ($makeDefault) {
                UserBankAccount::where('user_id', $userId)->update(['is_default' => false]);
            }

            return UserBankAccount::create([
                'user_id' => $userId,
                'paypal_id' => optional($existing->first())->paypal_id,
                'bank_name' => trim($data['bank_name']),
                'account_number' => $number,
                'routing_number' => isset($data['routing_number']) ? preg_replace('/\s+/', '', (string) $data['routing_number']) : null,
                'account_type' => $data['account_type'] ?? null,
                'is_default' => $makeDefault,
            ]);
        });
    }
}
