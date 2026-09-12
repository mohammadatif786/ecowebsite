<?php

namespace App\Http\Middleware;

use App\Models\UserOtp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->phone_verified_at) {
            return $next($request);
        }

        $hasActiveOtp = UserOtp::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();

        if (! $hasActiveOtp) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'OTP verification required.',
                'redirect' => route('verify.otp', ['token' => Crypt::encryptString((string) $user->id)]),
            ], 403);
        }

        return redirect()->route('verify.otp', ['token' => Crypt::encryptString((string) $user->id)]);
    }
}
