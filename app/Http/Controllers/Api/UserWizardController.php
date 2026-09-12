<?php

namespace App\Http\Controllers\Api;

use App\Actions\RegisteredUserAction;
use App\Actions\WizardUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisteredUserFormRequest;
use App\Http\Requests\WizardUpdateFormRequest;
use App\Models\User;
use App\Models\UserOtp;
use App\Notifications\ForgotPassword;
use App\Services\TwilioService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Str as SupportStr;

class UserWizardController extends Controller
{
    public function __construct(
        protected WizardUpdateAction    $wizardAction,
        protected RegisteredUserAction  $registeredAction
    ) {}

    // =========================================================================
    // REGISTER
    // =========================================================================

    /**
     * Check if a LinkUp ID is available (no auth required).
     *
     */
    public function checkLinkupId(Request $request): JsonResponse
    {
        $request->validate([
            'linkup_id' => 'required|string|min:3|max:20|regex:/^[a-z0-9_]+$/',
        ]);

        $result = $this->registeredAction->validateLinkUpId($request);

        return response()->json([
            'available' => $result['available'],
            'reason'    => $result['reason'] ?? null,
        ]);
    }

    /**
     * Register a new user account.
     * Reuses RegisteredUserFormRequest + RegisteredUserAction.
     *
     */
    public function register(RegisteredUserFormRequest $request, TwilioService $twilio): JsonResponse
    {
        $validated = $request->validated();

        $this->registeredAction->execute($validated);

        /** @var User $user */
        $user  = Auth::user();
        $input = $user->phone_number ?? $user->email;
        $token = $user->createToken($input)->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Account created successfully.',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    // =========================================================================
    // FORGOT PASSWORD
    // =========================================================================

    /**
     * Send a temporary password to the user's registered email.
     * Reuses the same ForgotPassword notification as the web flow.
     *
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => true,
                'message' => 'If this email exists, a temporary password has been sent.',
            ]);
        }

        $tempPassword = Str::random(8);
        $user->password = Hash::make($tempPassword);
        $user->save();

        $user->notify(new ForgotPassword([
            'name'          => $user->name ?? $user->first_name,
            'temp_password' => $tempPassword,
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'A temporary password has been sent to your email.',
        ]);
    }

    /**
     * Send an OTP to the provided phone number.
     *
     * Called at wizard step 4 (phone entry). Updates the user's phone_number,
     * generates a fresh OTP, sends it via Twilio SMS, and returns an encrypted
     * token that the client must pass to verifyOtp().
     *
     */
    public function sendOtp(Request $request, TwilioService $twilio): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'min:8'],
        ]);

        // Normalise: ensure leading '+'
        $phone = $validated['phone_number'];
        if (! str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($phone, '0');
        }

        // Block duplicate phone numbers (excluding the current user)
        $duplicate = User::where('phone_number', $phone)
            ->where('id', '!=', $user->id)
            ->first();

        if ($duplicate) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone number already in use. Please try a different number.',
            ], 422);
        }

        // Persist the new number and clear any previous verification
        $user->phone_number     = $phone;
        $user->phone_verified_at = null;
        $user->save();

        // Rotate OTPs: delete old, create fresh
        UserOtp::where('user_id', $user->id)->delete();
        $otp = rand(100000, 999999);
        UserOtp::create([
            'user_id'    => $user->id,
            'otp_hash'   => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'is_active'  => true,
            'attempts'   => 0,
        ]);

        // Dispatch SMS
        try {
            $twilio->sendSMS($phone, 'Link Up OTP code: ' . $otp);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Twilio API error (wizard send-otp): ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to send OTP. Please verify your number and try again.',
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully.',
            'token'   => Crypt::encryptString((string) $user->id),
        ]);
    }

    /**
     * Verify the OTP submitted by the mobile client.
     *
     */
    public function verifyOtp(Request $request, string $token): JsonResponse
    {
        // Decrypt the user ID from the token
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'OTP session expired or invalid. Please request a new OTP.',
            ], 422);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);
        }

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $otpRecord = UserOtp::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'OTP expired or invalid. Please request a new one.',
            ], 422);
        }

        // Check expiry
        if (now()->gt($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json([
                'status'  => false,
                'message' => 'OTP has expired. Please request a new one.',
            ], 422);
        }

        // Check value
        if (! Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');
            if ($otpRecord->attempts >= 3) {
                $otpRecord->update(['is_active' => false]);
            }
            return response()->json([
                'status'  => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        // Mark phone as verified and clean up
        $user->forceFill(['phone_verified_at' => now()])->save();
        $otpRecord->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Phone number verified successfully.',
        ]);
    }

    /**
     * Resend OTP to the already-stored phone number.
     *
     */
    public function resendOtp(Request $request, string $token, TwilioService $twilio): JsonResponse
    {
        try {
            $userId = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'OTP session expired or invalid.',
            ], 422);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        UserOtp::where('user_id', $user->id)->delete();
        $otp = rand(100000, 999999);
        UserOtp::create([
            'user_id'    => $user->id,
            'otp_hash'   => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'is_active'  => true,
            'attempts'   => 0,
        ]);

        try {
            $twilio->sendSMS($user->phone_number, 'Link Up OTP code: ' . $otp);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Twilio API error (wizard resend-otp): ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to resend OTP. Please verify your number.',
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'A new OTP has been sent.',
        ]);
    }

    /**
     * Complete the wizard by saving all profile fields.
     *
     * Reuses the same WizardUpdateFormRequest and WizardUpdateAction that the
     * web wizard uses, so validation rules stay in one place.
     *
     */
    public function wizardUpdate(WizardUpdateFormRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->wizardAction->update($validated, $request);

        return response()->json([
            'status'  => true,
            'message' => 'Wizard completed successfully.',
            'user'    => $user,
        ]);
    }
}
