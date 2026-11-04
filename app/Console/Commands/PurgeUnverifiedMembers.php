<?php

namespace App\Console\Commands;

use App\Actions\User\Signup\PurgeUnverifiedMembersAction;
use Illuminate\Console\Command;

class PurgeUnverifiedMembers extends Command
{
    protected $signature = 'signup:purge-unverified {--dry-run : Only count the accounts, delete nothing}';

    protected $description = 'Remove sign-ups whose email was never confirmed within the allowed days';

    public function handle(PurgeUnverifiedMembersAction $action): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $count = $action->execute($dryRun);

        $this->info(($dryRun ? 'Would remove ' : 'Removed ').$count.' unverified account(s) older than '.config('signup.unverified_purge_days').' days.');

        return self::SUCCESS;
    }
}
