<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The phone-code screens for existing members (verify, resend, change phone).
 * Sign in / sign out moved to User\Signup\SignInController.
 */
class AuthenticatedSessionController extends Controller
{
    public function VerifyOTP(string $token)
    {
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            abort(404);
        }

        $user = User::findOrFail($userId);

        // Only what the OTP screen needs — this page is reachable by token without login,
        // so the full user record must not be sent to the browser.
        return Inertia::render("User/Auth/OTPVerification", [
            'user' => [
                'has_phone' => ! empty($user->phone_number),
                'phone_masked' => $this->maskPhone($user->phone_number),
            ],
            'token' => $token,
            'open' => true,
            'redirectOnSuccess' => true,
        ]);
    }

    public function verify(Request $request, string $token)
    {
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'OTP expired or invalid'], 422);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['message' => 'OTP expired or invalid'], 422);
        }

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $otpRecord = UserOtp::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $otpRecord) {
            return response()->json(['message' => 'This code is no longer valid. Tap "Resend code" to get a new one.'], 422);
        }

        // Expired
        if (now()->gt($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json(['message' => 'OTP expired'], 422);
        }

        // Wrong OTP
        if (! Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');
            if ($otpRecord->attempts >= 3) {
                $otpRecord->update(['is_active' => false]);
                return response()->json(['message' => 'Too many wrong codes. Tap "Resend code" to get a new one.'], 429);
            }
            return response()->json(['message' => 'Invalid OTP'], 422);
        }
        // OTP VALID → LOGIN USER
        $user = $otpRecord->user;
        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();
        Auth::login($user, (bool) $request->session()->pull('otp_remember', false));
        // Cleanup
        $otpRecord->delete();
        $request->session()->regenerate();

        $redirectUrl = $request->session()->pull('url.intended', route('user.feed', absolute: false));
        return response()->json(['redirect' => $redirectUrl]);
    }

    public function resendOtp(Request $request, string $token, TwilioService $twilio)
    {
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'OTP expired or invalid'], 422);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (empty($user->phone_number)) {
            return response()->json(['message' => 'Add your phone number first.'], 422);
        }

        // Invalidate previous OTPs
        UserOtp::where('user_id', $user->id)->delete();
        // Generate new OTP
        $otp = random_int(100000, 999999);
        UserOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'is_active' => true,
            'attempts' => 0,
        ]);
        // Optionally send OTP via email/SMS here
        try {
            $twilio->sendSMS(
                $user->phone_number,
                'Link Up Otp code: ' . $otp
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Twilio Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP to this phone. Please check if the number is correct. Reason: ' . $e->getMessage(),
            ], 422);
        }
        return response()->json([
            'message' => 'A new code has been sent.',
        ]);
    }

    public function changePhone(Request $request, TwilioService $twilio)
    {
        $token = $request->route('token');

        if ($token) {
            try {
                $userId = Crypt::decryptString($token);
            } catch (\Throwable $e) {
                return response()->json(['message' => 'OTP expired or invalid'], 422);
            }

            $user = User::find($userId);
            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        } else {
            $user = $request->user();
            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8'],
        ]);

        $phone = $validated['phone'];
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($phone, '0'); // also strip leading 0 if any
        }

        // Check if phone number already exists (excluding current user)
        $existingPhone = \App\Models\User::where('phone_number', $phone)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingPhone) {
            return response()->json(['message' => 'Phone number already exists, try another phone number'], 422);
        }

        // Update phone
        $user->phone_number = $phone;
        $user->phone_verified_at = null;
        $user->save();

        // Reset existing OTPs and create a new one
        UserOtp::where('user_id', $user->id)->delete();
        $otp = random_int(100000, 999999);
        UserOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'is_active' => true,
            'attempts' => 0,
        ]);

        // Send the OTP to the updated phone number
        try {
            $twilio->sendSMS(
                $user->phone_number,
                'Link Up Otp code: ' . $otp
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Twilio Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP to this phone. Please verify your number. Reason: ' . $e->getMessage(),
            ], 422);
        }

        $tokenToReturn = $token ?: Crypt::encryptString((string) $user->id);
        return response()->json([
            'message' => 'Phone number updated. A new code has been sent.',
            'token' => $tokenToReturn,
            'phone_masked' => $this->maskPhone($user->phone_number),
        ]);
    }

    /**
     * "+18761234567" → "+1 ••••••4567". Empty → null.
     */
    private function maskPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) <= 4) {
            return $phone;
        }
        $prefix = str_starts_with($phone, '+') ? '+' . substr($digits, 0, 1) . ' ' : '';
        $hidden = strlen($digits) - 4 - ($prefix ? 1 : 0);
        return $prefix . str_repeat('•', max($hidden, 0)) . substr($digits, -4);
    }
}
