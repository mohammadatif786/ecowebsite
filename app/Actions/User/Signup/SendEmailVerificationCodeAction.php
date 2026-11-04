<?php

namespace App\Actions\User\Signup;

use App\Mail\Signup\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Emails a fresh 6-digit code to the member's current address.
 *
 * A new send replaces the previous code. Two limits stop it being used to spam an inbox or to
 * keep guessing: a short wait between sends, and a cap per hour.
 */
class SendEmailVerificationCodeAction
{
    /**
     * @param  string  $errorKey  form field the page shows a problem under ("code" or "email")
     *
     * @throws ValidationException
     */
    public function execute(User $user, string $errorKey = 'code'): void
    {
        if ($user->email_verified_at !== null) {
            return; // nothing left to confirm
        }

        $config = config('signup.email_code');

        $wait = $this->secondsUntilResend($user);
        if ($wait > 0) {
            throw ValidationException::withMessages([
                $errorKey => "Please wait {$wait} seconds before asking for another code.",
            ]);
        }

        $limiterKey = 'signup-email-code:'.$user->id;
        if (RateLimiter::tooManyAttempts($limiterKey, $config['max_sends_per_hour'])) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($limiterKey) / 60));
            throw ValidationException::withMessages([
                $errorKey => "Too many codes requested. Try again in {$minutes} minutes.",
            ]);
        }
        RateLimiter::hit($limiterKey, 3600);

        $code = str_pad((string) random_int(0, (10 ** $config['length']) - 1), $config['length'], '0', STR_PAD_LEFT);

        EmailVerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'code_hash' => EmailVerificationCode::hashFor($code, $user->email),
                'attempts' => 0,
                'expires_at' => now()->addMinutes($config['ttl_minutes']),
                'last_sent_at' => now(),
            ],
        );

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMail($user, $code, $config['ttl_minutes']));
        } catch (Throwable $e) {
            report($e);
            EmailVerificationCode::where('user_id', $user->id)->delete();

            throw ValidationException::withMessages([
                $errorKey => "We couldn't send the code. Check your email address and try again.",
            ]);
        }
    }

    /**
     * Seconds the member still has to wait before another code can be sent. 0 means now.
     */
    public function secondsUntilResend(User $user): int
    {
        $record = EmailVerificationCode::where('user_id', $user->id)->first();

        if (! $record || $record->email !== $user->email) {
            return 0;
        }

        $availableAt = $record->last_sent_at->copy()->addSeconds(config('signup.email_code.resend_cooldown_seconds'));

        return $availableAt->isFuture() ? max(1, (int) ceil(now()->diffInSeconds($availableAt, false))) : 0;
    }
}
