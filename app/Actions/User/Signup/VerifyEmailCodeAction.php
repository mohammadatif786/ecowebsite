<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\EmailCodeData;
use App\Jobs\RegisterWelcomeEmail;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Checks the 6-digit code and, only when it matches, marks the email as verified.
 *
 * This is the only place `email_verified_at` is set for a new member. A wrong guess is counted
 * inside the same transaction that reads the code, so parallel requests cannot get extra tries.
 */
class VerifyEmailCodeAction
{
    /**
     * @throws ValidationException when the code is wrong, expired, locked or was never sent
     */
    public function execute(EmailCodeData $data): void
    {
        $user = $data->user;

        if ($user->email_verified_at !== null) {
            return;
        }

        $config = config('signup.email_code');

        // The result is decided inside the transaction and thrown after it. Throwing inside would
        // roll back the wrong-guess counter.
        $outcome = DB::transaction(function () use ($user, $data, $config) {
            $record = EmailVerificationCode::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $record || $record->email !== $user->email) {
                return ['missing', 0];
            }

            if ($record->expires_at->isPast()) {
                $record->delete();

                return ['expired', 0];
            }

            if ($record->attempts >= $config['max_attempts']) {
                return ['locked', 0];
            }

            $matches = strlen($data->code) === $config['length']
                && hash_equals($record->code_hash, EmailVerificationCode::hashFor($data->code, $record->email));

            if (! $matches) {
                $record->increment('attempts');
                $left = $config['max_attempts'] - $record->attempts;

                return $left > 0 ? ['wrong', $left] : ['locked', 0];
            }

            $user->forceFill(['email_verified_at' => now()])->save();
            $record->delete();

            return ['ok', 0];
        });

        [$status, $left] = $outcome;

        if ($status !== 'ok') {
            throw ValidationException::withMessages(['code' => match ($status) {
                'wrong' => $left === 1 ? "That code isn't right. 1 try left." : "That code isn't right. {$left} tries left.",
                'locked' => 'Too many wrong codes. Tap "Resend code" to get a new one.',
                'expired' => 'That code has expired. Tap "Resend code" to get a new one.',
                default => 'No code is waiting for this email. Tap "Resend code" to get one.',
            }]);
        }

        event(new Verified($user));

        // The welcome email waits until the address is confirmed, so it never goes to a mistyped one.
        if ((bool) $user->requires_email_verification) {
            RegisterWelcomeEmail::dispatch($user)->delay(now()->addMinutes(2));
        }
    }
}
