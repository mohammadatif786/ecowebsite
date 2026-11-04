<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\SendPhoneOtpAction;
use App\Actions\User\Signup\VerifyPhoneOtpAction;
use App\DTOs\User\Signup\PhoneNumberData;
use App\DTOs\User\Signup\PhoneOtpData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Signup\SendPhoneOtpRequest;
use App\Http\Requests\User\Signup\VerifyPhoneOtpRequest;
use Illuminate\Http\JsonResponse;

/**
 * Wizard step 3 (phone). JSON endpoints for the wizard to call while the member is signed in.
 * Both routes sit behind `auth` and `signup.email`, so they cannot be reached before the email is confirmed.
 */
class PhoneVerificationController extends Controller
{
    public function send(SendPhoneOtpRequest $request, SendPhoneOtpAction $action): JsonResponse
    {
        $sent = $action->execute(PhoneNumberData::fromValidated($request->user(), $request->validated()));

        return response()->json([
            'sent' => $sent,
            'verified' => ! $sent,
            'message' => $sent ? 'We sent you a 6-digit code.' : 'This number is already verified.',
            'resend_available_in' => $sent ? (int) config('signup.phone_otp.resend_cooldown_seconds') : 0,
        ]);
    }

    public function verify(VerifyPhoneOtpRequest $request, VerifyPhoneOtpAction $action): JsonResponse
    {
        $action->execute(PhoneOtpData::fromValidated($request->user(), $request->validated()));

        return response()->json([
            'verified' => true,
            'message' => 'Phone number verified.',
        ]);
    }
}
