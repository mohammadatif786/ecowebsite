<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\PhoneOtpData;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Checks the texted code for the signed-in member and sets `phone_verified_at`.
 * Three wrong codes kill the code; the member has to ask for a new one.
 */
class VerifyPhoneOtpAction
{
    /**
     * @throws ValidationException
     */
    public function execute(PhoneOtpData $data): void
    {
        $user = $data->user;

        if ($user->phone_verified_at !== null) {
            return;
        }

        if (blank($user->phone_number)) {
            throw ValidationException::withMessages(['otp' => 'Add your phone number first.']);
        }

        $maxAttempts = config('signup.phone_otp.max_attempts');

        // Decided inside the transaction, thrown after it, so the wrong-guess counter is kept.
        $status = DB::transaction(function () use ($user, $data, $maxAttempts) {
            $record = UserOtp::where('user_id', $user->id)
                ->where('is_active', true)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return 'missing';
            }

            if ($record->expires_at->isPast()) {
                $record->delete();

                return 'expired';
            }

            if (! Hash::check($data->otp, $record->otp_hash)) {
                $record->increment('attempts');

                if ($record->attempts >= $maxAttempts) {
                    $record->update(['is_active' => false]);

                    return 'locked';
                }

                return 'wrong';
            }

            $user->forceFill(['phone_verified_at' => now()])->save();
            $record->delete();

            return 'ok';
        });

        if ($status !== 'ok') {
            throw ValidationException::withMessages(['otp' => match ($status) {
                'wrong' => "That code isn't right.",
                'locked' => 'Too many wrong codes. Tap "Resend code" to get a new one.',
                'expired' => 'That code has expired. Tap "Resend code" to get a new one.',
                default => 'No code is waiting. Tap "Resend code" to get one.',
            }]);
        }
    }
}
