<?php

namespace App\Domain\Wallet\Actions;

use App\Domain\Wallet\WalletActivity;
use App\Models\BankWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

/**
 * The only place a pending bank withdrawal changes state.
 * The row is re-read under a lock INSIDE the transaction, so an approve, a reject and a member's
 * cancel can never both succeed, and a refund can never be paid twice.
 */
final class SettleBankWithdrawalAction
{
    public function approve(int $withdrawalId, int $adminId): BankWithdrawal
    {
        $withdrawal = DB::transaction(function () use ($withdrawalId) {
            $w = $this->lockPending($withdrawalId);
            $w->update(['status' => 'completed', 'processed_at' => now()]);

            return $w;
        });

        $this->record('bank_withdrawal_approved', $withdrawal, $adminId, 'admin', 'Approved');
        $this->notifyMember($withdrawal->refresh());

        return $withdrawal;
    }

    public function reject(int $withdrawalId, int $adminId, ?string $reason): BankWithdrawal
    {
        $reason = trim((string) $reason) ?: 'Rejected by admin';

        $withdrawal = DB::transaction(function () use ($withdrawalId, $adminId, $reason) {
            $w = $this->lockPending($withdrawalId);
            $this->refund($w, ['type' => 'bank_withdrawal_refund', 'withdrawal_id' => $w->id, 'admin_id' => $adminId,
                'note' => 'Withdrawal rejected by admin - amount refunded']);
            $w->update(['status' => 'failed', 'failure_reason' => mb_substr($reason, 0, 500), 'processed_at' => now()]);

            return $w;
        });

        $this->record('bank_withdrawal_rejected', $withdrawal, $adminId, 'admin', 'Rejected: ' . $reason);
        $this->notifyMember($withdrawal->refresh());

        return $withdrawal;
    }

    /** Member cancels their own pending withdrawal (time window is checked by the caller). */
    public function cancelByMember(int $withdrawalId, int $userId, callable $guard): BankWithdrawal
    {
        return DB::transaction(function () use ($withdrawalId, $userId, $guard) {
            $w = $this->lockPending($withdrawalId, $userId);
            $guard($w);
            $this->refund($w, ['type' => 'bank_withdrawal_refund', 'withdrawal_id' => $w->id, 'note' => 'Withdrawal cancelled - amount refunded']);
            $w->update(['status' => 'cancelled', 'failure_reason' => 'Cancelled by user', 'processed_at' => now()]);

            return $w->refresh();
        });
    }

    private function lockPending(int $withdrawalId, ?int $userId = null): BankWithdrawal
    {
        $w = BankWithdrawal::query()
            ->whereKey($withdrawalId)
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->lockForUpdate()
            ->firstOrFail();

        if ($w->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['This withdrawal was already handled (' . $w->status . ').'],
            ]);
        }

        return $w;
    }

    private function refund(BankWithdrawal $w, array $meta): void
    {
        $user = User::findOrFail($w->user_id);

        transfer($w->amount, 'USD')
            ->from(Custodian::of('system'))
            ->to($user)
            ->meta($meta)
            ->commit();
    }

    /** Same e-mail the old admin page sent (BankWithDrawalJob). */
    private function notifyMember(BankWithdrawal $w): void
    {
        $w->loadMissing('user');
        $safe = fn (callable $f) => \App\Helpers\UserBankAccountHelper::safeDecrypt($f);

        \App\Jobs\BankWithDrawalJob::dispatch([
            'user_name' => $w->user->name ?? 'User',
            'user_email' => $w->user->email ?? 'Unknown',
            'total_amount' => $w->amount,
            'fee_amount' => $w->fee_amount,
            'payout_amount' => $w->payout_amount,
            'bank_name' => $safe(fn () => $w->bank_name) ?? 'Unknown Bank',
            'account_number' => $safe(fn () => $w->account_number) ?? 'Unknown Account',
            'note' => $w->status === 'completed'
                ? 'Your bank withdrawal has been approved and is being processed.'
                : 'Your bank withdrawal has been rejected.' . ($w->failure_reason ? " Reason: {$w->failure_reason}" : ''),
        ]);
    }

    private function record(string $type, BankWithdrawal $w, int $actorId, string $actorType, string $message): void
    {
        WalletActivity::record($type, [
            'user_id' => $w->user_id,
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'amount' => (float) $w->amount,
            'reference' => 'withdrawal #' . $w->id,
            'message' => $message,
        ]);
    }
}
