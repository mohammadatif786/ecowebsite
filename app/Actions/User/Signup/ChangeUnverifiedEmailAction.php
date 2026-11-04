<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\ChangeEmailData;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * "Change email" on the Verify email page. Only allowed while the email is still unverified;
 * the old code is discarded and a new one goes to the new address.
 */
class ChangeUnverifiedEmailAction
{
    public function __construct(private readonly SendEmailVerificationCodeAction $sendCode) {}

    /**
     * @throws ValidationException
     */
    public function execute(ChangeEmailData $data): void
    {
        $user = $data->user;

        if ($user->email_verified_at !== null) {
            throw ValidationException::withMessages(['email' => 'Your email is already verified.']);
        }

        // Same address typed again: treat it as "send the code again".
        if ($data->email === strtolower($user->email)) {
            $this->sendCode->execute($user, 'email');

            return;
        }

        $taken = User::where('email', $data->email)->where('id', '!=', $user->id)->exists();
        if ($taken) {
            throw ValidationException::withMessages(['email' => 'That email is already registered.']);
        }

        DB::transaction(function () use ($user, $data) {
            $user->forceFill(['email' => $data->email])->save();
            EmailVerificationCode::where('user_id', $user->id)->delete(); // it belonged to the old address
        });

        $this->sendCode->execute($user->refresh(), 'email');
    }
}
