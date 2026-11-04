<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\RegisterMemberData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * "Create account". Makes the member, flags the email as still to be confirmed, emails the code
 * and signs them in. Signed in does not mean let in: every member route sits behind the
 * onboarding gate until the email code is entered.
 */
class RegisterMemberAction
{
    public function __construct(private readonly SendEmailVerificationCodeAction $sendCode) {}

    public function execute(RegisterMemberData $data): User
    {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data->fullName(),
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'type' => 'user',
                'email' => $data->email,
                'linkup_id' => $data->linkupId,
                'password' => $data->password,
            ]);

            // Not mass-assignable on purpose: only this Action turns the gate on.
            $user->forceFill(['requires_email_verification' => true])->save();

            return $user;
        });

        event(new Registered($user));

        try {
            $this->sendCode->execute($user);
        } catch (Throwable $e) {
            // The account exists either way. The Verify email page has "Resend code".
            report($e);
        }

        Auth::login($user);

        return $user;
    }
}
