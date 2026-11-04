<?php

namespace App\Actions\User\Signup;

use Illuminate\Support\Facades\Password;

/**
 * "Forgot password?": emails a reset link to the account with this address.
 *
 * The page always gets the same answer, whether or not an account uses the address and whether or
 * not a link was sent a moment ago, so nobody can use it to find out who has a LinkUp account.
 * The broker already limits each account to one link per minute (config/auth.php).
 */
class SendPasswordResetLinkAction
{
    public function execute(string $email): void
    {
        Password::broker()->sendResetLink(['email' => $email]);
    }
}
