<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\User\Signup\EmailVerificationController;
use App\Http\Controllers\User\Signup\PasswordResetController;
use App\Http\Controllers\User\Signup\RegisterController;
use App\Http\Controllers\User\Signup\SignInController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Create account and Sign in are two tabs of the same page (controllers in User/Signup).
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    // Each account sends an email, so sign-ups are limited per IP.
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:10,10,register:');
    Route::post('check-linkup-id', [RegisterController::class, 'checkLinkupId'])->middleware('throttle:60,1,linkup-id:')->name('check.linkup.id');
    Route::get('login', [SignInController::class, 'create'])->name('login');
    Route::post('login', [SignInController::class, 'store']);
    // Forgot password: email a reset link, then choose a new password (controller in User/Signup).
    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:6,10,password-email:')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:10,10,password-reset:')->name('password.store');
});

// OTP endpoints are rate-limited per IP: 3 wrong codes already kill a code (controller),
// these limits stop an attacker from cycling Resend to keep guessing, and limit SMS spend.
Route::post('change-phone', [AuthenticatedSessionController::class, 'changePhone'])->middleware('throttle:5,10,otp-send')->name('change.phone');
Route::get('verify-otp/{token}', [AuthenticatedSessionController::class, 'VerifyOTP'])->name('verify.otp');
Route::post('verify/{token}', [AuthenticatedSessionController::class, 'verify'])->middleware('throttle:10,10,otp-verify')->name('verify');
Route::post('verify-otp/{token}/resend', [AuthenticatedSessionController::class, 'resendOtp'])->middleware('throttle:5,10,otp-send')->name('verify.otp.resend');
Route::post('verify-otp/{token}/change-phone', [AuthenticatedSessionController::class, 'changePhone'])->middleware('throttle:5,10,otp-send')->name('verify.otp.change_phone');

Route::middleware('auth')->group(function () {
    // Verify email: the 6-digit code page that follows "Create account". Signed in only, no other gate,
    // because this is where an unverified member is held.
    Route::get('verify-email', [EmailVerificationController::class, 'show'])->name('verification.notice');
    Route::post('verify-email/code', [EmailVerificationController::class, 'verify'])->middleware('throttle:20,10,email-code:')->name('verification.code');
    Route::post('verify-email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,10,email-resend:')->name('verification.send');
    Route::post('verify-email/change', [EmailVerificationController::class, 'change'])->middleware('throttle:6,10,email-change:')->name('verification.change');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::post('logout', [SignInController::class, 'destroy'])->name('logout');
});
