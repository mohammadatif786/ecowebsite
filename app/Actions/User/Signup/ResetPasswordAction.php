<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\PasswordResetData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Sets the new password from a reset link. The link is single-use and expires (config/auth.php).
 *
 * Opening the link proves the member reads that inbox, so an email that was never confirmed counts
 * as confirmed from here on. Every other signed-in session is signed out by the new remember token.
 */
class ResetPasswordAction
{
    /**
     * @throws ValidationException
     */
    public function execute(PasswordResetData $data): User
    {
        $member = null;

        $status = Password::broker()->reset(
            ['email' => $data->email, 'token' => $data->token, 'password' => $data->password],
            function (User $user, string $password) use (&$member) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                event(new PasswordReset($user));
                $member = $user;
            },
        );

        if ($status !== Password::PASSWORD_RESET || ! $member) {
            throw ValidationException::withMessages([
                'token' => 'This reset link is no longer valid. Links work once and expire after '.config('auth.passwords.users.expire').' minutes. Ask for a new one.',
            ]);
        }

        return $member;
    }
}
