<?php

namespace App\Domain\Wallet;

use App\Actions\InitiateBankWithdrawalAction;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserWalletKyc;

/**
 * Extra wallet-hub props: saved banks (masked), withdrawal fee, KYC status and whether a PIN is set.
 */
final class WalletPageData
{
    public static function bankAccounts(int $userId): array
    {
        return UserBankAccount::where('user_id', $userId)->orderBy('id')->get()
            ->filter(fn ($a) => ! empty($a->bank_name) && ! empty($a->account_number))
            ->map(fn (UserBankAccount $a) => [
                'id' => $a->id,
                'bank_name' => $a->bank_name,
                'last4' => substr((string) $a->account_number, -4),
                'account_type' => $a->account_type,
                'is_default' => (bool) $a->is_default,
            ])
            ->values()
            ->all();
    }

    public static function kyc(int $userId): array
    {
        $kyc = UserWalletKyc::where('user_id', $userId)->first(['id', 'status', 'updated_at']);

        return [
            'status' => $kyc?->status ?: 'none', // none | pending | approved | rejected
            'updated_at' => optional($kyc?->updated_at)->toISOString(),
        ];
    }

    public static function forUser(User $user): array
    {
        return [
            'bankAccounts' => self::bankAccounts($user->id),
            'withdrawalFeePercent' => InitiateBankWithdrawalAction::feePercent(),
            'kyc' => self::kyc($user->id),
            'hasPin' => ! empty($user->transaction_pin),
        ];
    }
}
