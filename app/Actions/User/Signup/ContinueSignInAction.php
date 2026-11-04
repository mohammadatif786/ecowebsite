<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\OnboardingStage;
use App\DTOs\User\Signup\SignInDestination;
use App\DTOs\User\Signup\SignInOutcome;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Decides what happens after a correct password.
 *
 *  1. Website sign-up that never entered the email code: held on the Verify email page.
 *  2. Phone already verified: straight into the app.
 *  3. Existing member with an unverified phone: texted a code and signed out until it is
 *     confirmed (the old behaviour, unchanged).
 *
 * Session handling (signing out, regenerating, redirecting) stays in the controller.
 */
class ContinueSignInAction
{
    public function __construct(
        private ResolveOnboardingStateAction $resolveState,
        private TwilioService $twilio,
    ) {}

    public function execute(User $member): SignInOutcome
    {
        if ($this->resolveState->execute($member)->stage === OnboardingStage::Email) {
            return new SignInOutcome(SignInDestination::VerifyEmail);
        }

        if ($member->phone_verified_at) {
            return new SignInOutcome(SignInDestination::App);
        }

        $this->sendPhoneCode($member);

        return new SignInOutcome(SignInDestination::PhoneCode, Crypt::encryptString((string) $member->id));
    }

    private function sendPhoneCode(User $member): void
    {
        UserOtp::where('user_id', $member->id)->delete();

        $code = random_int(100000, 999999);

        UserOtp::create([
            'user_id' => $member->id,
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'is_active' => true,
            'attempts' => 0,
        ]);

        if (! $member->phone_number) {
            return; // They add a number on the code screen.
        }

        try {
            $this->twilio->sendSMS($member->phone_number, 'Link Up Otp code: '.$code);
        } catch (\Throwable $e) {
            // The code screen lets them resend or change the number.
            Log::warning('Sign-in phone code could not be sent', ['user_id' => $member->id, 'error' => $e->getMessage()]);
        }
    }
}
