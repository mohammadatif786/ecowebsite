<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('User/Auth/Register', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, TwilioService $twilio)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $request->authenticate();
        $user = User::where('email', $request->email)->first();

        if ($user && $user->phone_verified_at) {
            return redirect()->intended(route('new_frontend.home', absolute: false));
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        // Remove old OTPs
        UserOtp::where('user_id', $user->id)->delete();
        // Save new OTP
        UserOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'is_active' => true,
            'attempts' => 0,
        ]);

        if ($user->phone_number) {
            try {
                // Optionally send OTP via email/SMS here
                $twilio->sendSMS(
                    $user->phone_number,
                    'Link Up Otp code: ' . $otp
                );
            } catch (\Exception $e) {
                // Error gracefully skipped to let user change their phone
            }
        }

        $token = Crypt::encryptString((string) $user->id);
        return redirect()->route('verify.otp', ['token' => $token]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user instanceof \Illuminate\Database\Eloquent\Model) {
            $user->update([
                'is_active' => 0,
                'last_active' => now(),
            ]);
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/register');
    }


    public function VerifyOTP(string $token)
    {
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            abort(404);
        }

        $user = User::findOrFail($userId);

        return Inertia::render("User/Auth/OTPVerification", [
            'user' => $user,
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
            return response()->json(['message' => 'OTP expired or invalid'], 422);
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
            }
            return response()->json(['message' => 'Invalid OTP'], 422);
        }
        // OTP VALID → LOGIN USER
        $user = $otpRecord->user;
        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();
        Auth::login($user);
        // Cleanup
        $otpRecord->delete();
        $request->session()->regenerate();

        $redirectUrl = route('new_frontend.home', absolute: false);
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

        // Invalidate previous OTPs
        UserOtp::where('user_id', $user->id)->delete();
        // Generate new OTP
        $otp = rand(100000, 999999);
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
        $otp = rand(100000, 999999);
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
        ]);
    }
}
