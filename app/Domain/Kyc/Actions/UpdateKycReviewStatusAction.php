<?php

namespace App\Domain\Kyc\Actions;

use App\Models\OrganizerKyc;
use App\Models\User;
use App\Models\UserWalletKyc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sets the review status of one KYC record.
 *
 * Wallet KYC only unlocks the wallet: approving it never changes the user's type or roles.
 * Organizer KYC (and profile KYC, which feeds the organizer flow) keeps the organizer upgrade.
 */
final class UpdateKycReviewStatusAction
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function execute(string $source, int $id, string $status): void
    {
        abort_unless(in_array($status, self::STATUSES, true), 422);

        DB::transaction(function () use ($source, $id, $status) {
            $userToUpgrade = match ($source) {
                'wallet' => $this->updateWallet($id, $status),
                'organizer' => $this->updateOrganizer($id, $status),
                'user' => $this->updateUser($id, $status),
                default => abort(404),
            };

            if ($userToUpgrade) {
                $this->upgradeToOrganizer($userToUpgrade, $source);
            }
        });

        \App\Domain\Wallet\WalletActivity::record('kyc_reviewed', [
            'user_id' => $this->subjectUserId($source, $id),
            'actor_type' => 'admin',
            'reference' => $source . ' KYC #' . $id,
            'message' => 'Set to ' . $status,
            'meta' => ['source' => $source, 'status' => $status],
        ]);

        Log::info('KYC review status changed', [
            'admin_id' => auth()->id(),
            'source' => $source,
            'record_id' => $id,
            'status' => $status,
        ]);
    }

    private function subjectUserId(string $source, int $id): ?int
    {
        return match ($source) {
            'wallet' => UserWalletKyc::whereKey($id)->value('user_id'),
            'organizer' => OrganizerKyc::with('organizer:id,user_id')->find($id)?->organizer?->user_id,
            'user' => $id,
            default => null,
        };
    }

    private function updateWallet(int $id, string $status): null
    {
        UserWalletKyc::whereKey($id)->lockForUpdate()->firstOrFail()->update(['status' => $status]);

        return null;
    }

    private function updateOrganizer(int $id, string $status): ?User
    {
        $stored = $status === 'rejected' ? 'canceled' : $status;
        $kyc = OrganizerKyc::whereKey($id)->lockForUpdate()->firstOrFail();

        $kyc->update([
            'status' => $stored,
            'p_front_status' => $stored,
            'p_back_status' => $stored,
            'p_o_add_status' => $stored,
        ]);

        return $stored === 'approved' ? $kyc->organizer?->user : null;
    }

    private function updateUser(int $id, string $status): ?User
    {
        $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
        $user->update(['kyc_status' => $status]);

        return $status === 'approved' ? $user : null;
    }

    private function upgradeToOrganizer(User $user, string $source): void
    {
        if ($user->type === 'admin') {
            return;
        }

        $user->update(['type' => 'organizer']);
        $user->syncRoles(['organizer']);

        Log::info('User upgraded to organizer via KYC approval', [
            'user_id' => $user->id,
            'source' => $source,
        ]);
    }
}
