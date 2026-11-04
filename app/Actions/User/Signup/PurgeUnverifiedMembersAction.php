<?php

namespace App\Actions\User\Signup;

use App\Models\User;
use App\Models\UserOtp;
use Throwable;

/**
 * Removes accounts made through the new sign-up whose email was never confirmed.
 *
 * Those accounts could not reach anything but the Verify email page, so there is no activity to
 * lose; removing them frees the email address and LinkUp ID for the real owner.
 */
class PurgeUnverifiedMembersAction
{
    /**
     * @return int number of accounts removed (or that would be removed, on a dry run)
     */
    public function execute(bool $dryRun = false): int
    {
        $cutoff = now()->subDays((int) config('signup.unverified_purge_days'));
        $count = 0;

        User::query()
            ->where('type', 'user')
            ->where('requires_email_verification', true)
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($members) use ($dryRun, &$count) {
                foreach ($members as $member) {
                    if ($dryRun) {
                        $count++;

                        continue;
                    }

                    try {
                        UserOtp::where('user_id', $member->id)->delete();
                        $member->delete(); // email_verification_codes rows go with it (cascade)
                        $count++;
                    } catch (Throwable $e) {
                        report($e); // one stuck account must not stop the rest
                    }
                }
            });

        return $count;
    }
}
