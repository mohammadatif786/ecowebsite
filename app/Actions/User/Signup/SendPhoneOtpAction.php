<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\PhoneNumberData;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Wizard step 3: saves the phone number on the signed-in member and texts a 6-digit code.
 *
 * Same table and hashing as the sign-in OTP (user_otps), so both flows can verify it. Unlike the
 * old token routes this one works only for the signed-in member, so nobody can start a text to
 * somebody else's account.
 */
class SendPhoneOtpAction
{
    public function __construct(private readonly TwilioService $twilio) {}

    /**
     * @return bool true when a code was sent, false when this number is already verified for the member
     *
     * @throws ValidationException
     */
    public function execute(PhoneNumberData $data): bool
    {
        $user = $data->user;
        $config = config('signup.phone_otp');

        if (! preg_match('/^\+[1-9]\d{7,14}$/', $data->phone)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Enter a valid phone number with its country code.',
            ]);
        }

        $usedByOther = User::where('phone_number', $data->phone)->where('id', '!=', $user->id)->exists();
        if ($usedByOther) {
            throw ValidationException::withMessages([
                'phone_number' => 'This phone number is already in use. Please try a different number.',
            ]);
        }

        $sameNumber = filled($user->phone_number)
            && PhoneNumberData::digits($user->phone_number) === PhoneNumberData::digits($data->phone);

        if ($sameNumber && $user->phone_verified_at !== null) {
            return false;
        }

        // Re-sending to the same number waits a little; a different number does not.
        if ($sameNumber) {
            $recent = UserOtp::where('user_id', $user->id)
                ->where('created_at', '>', now()->subSeconds($config['resend_cooldown_seconds']))
                ->latest('id')
                ->first();

            if ($recent) {
                $wait = max(1, (int) ceil(now()->diffInSeconds($recent->created_at->copy()->addSeconds($config['resend_cooldown_seconds']), false)));
                throw ValidationException::withMessages([
                    'phone_number' => "Please wait {$wait} seconds before asking for another code.",
                ]);
            }
        }

        $limiterKey = 'signup-phone-otp:'.$user->id;
        if (RateLimiter::tooManyAttempts($limiterKey, $config['max_sends_per_hour'])) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($limiterKey) / 60));
            throw ValidationException::withMessages([
                'phone_number' => "Too many codes requested. Try again in {$minutes} minutes.",
            ]);
        }
        RateLimiter::hit($limiterKey, 3600);

        // A changed number is never verified until its own code is entered.
        $user->forceFill(['phone_number' => $data->phone, 'phone_verified_at' => null])->save();

        UserOtp::where('user_id', $user->id)->delete();

        $otp = random_int(100000, 999999);
        UserOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make((string) $otp),
            'expires_at' => now()->addMinutes($config['ttl_minutes']),
            'is_active' => true,
            'attempts' => 0,
        ]);

        try {
            $this->twilio->sendSMS($data->phone, 'Link Up OTP code: '.$otp);
        } catch (Throwable $e) {
            Log::error('Phone OTP send failed: '.$e->getMessage());
            UserOtp::where('user_id', $user->id)->delete();

            throw ValidationException::withMessages([
                'phone_number' => "We couldn't text that number. Check it and try again.",
            ]);
        }

        return true;
    }
}
