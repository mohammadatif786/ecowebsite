<?php

namespace App\Console\Commands\Kyc;

use App\Models\OrganizerKyc;
use App\Models\User;
use App\Models\UserWalletKyc;
use Illuminate\Console\Command;

/**
 * Read-only report: users whose type is "organizer" and who have an approved wallet KYC but no
 * approved organizer KYC. Before the fix, approving a wallet KYC made a user an organizer, so
 * these accounts may have been upgraded by mistake. Nothing is changed.
 */
class ListWalletKycOrganizerUpgrades extends Command
{
    protected $signature = 'kyc:wallet-upgrades';

    protected $description = 'List organizers who may have been upgraded only by a wallet KYC approval (read-only)';

    public function handle(): int
    {
        $approvedOrganizerUserIds = OrganizerKyc::query()
            ->where('status', 'approved')
            ->with('organizer:id,user_id')
            ->get()
            ->pluck('organizer.user_id')
            ->filter()
            ->unique();

        $rows = User::query()
            ->where('type', 'organizer')
            ->whereIn('id', UserWalletKyc::where('status', 'approved')->select('user_id'))
            ->whereNotIn('id', $approvedOrganizerUserIds)
            ->where(fn ($q) => $q->whereNull('kyc_status')->orWhere('kyc_status', '!=', 'approved'))
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'type', 'created_at'])
            ->map(fn (User $u) => [$u->id, $u->name, $u->email, $u->type, optional($u->created_at)->toDateString()]);

        if ($rows->isEmpty()) {
            $this->info('No organizers found whose only approval is a wallet KYC.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Name', 'Email', 'Type', 'Joined'], $rows->all());
        $this->info($rows->count() . ' account(s). Nothing was changed.');

        return self::SUCCESS;
    }
}
