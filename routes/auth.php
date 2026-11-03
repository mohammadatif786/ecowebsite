<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('check-linkup-id', [RegisteredUserController::class, 'checkLinkupId'])->name('check.linkup.id');
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// OTP endpoints are rate-limited per IP: 3 wrong codes already kill a code (controller),
// these limits stop an attacker from cycling Resend to keep guessing, and limit SMS spend.
Route::post('change-phone', [AuthenticatedSessionController::class, 'changePhone'])->middleware('throttle:5,10,otp-send')->name('change.phone');
Route::get('verify-otp/{token}', [AuthenticatedSessionController::class, 'VerifyOTP'])->name('verify.otp');
Route::post('verify/{token}', [AuthenticatedSessionController::class, 'verify'])->middleware('throttle:10,10,otp-verify')->name('verify');
Route::post('verify-otp/{token}/resend', [AuthenticatedSessionController::class, 'resendOtp'])->middleware('throttle:5,10,otp-send')->name('verify.otp.resend');
Route::post('verify-otp/{token}/change-phone', [AuthenticatedSessionController::class, 'changePhone'])->middleware('throttle:5,10,otp-send')->name('verify.otp.change_phone');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
