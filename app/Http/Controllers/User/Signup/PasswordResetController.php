<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\ResetPasswordAction;
use App\Actions\User\Signup\SendPasswordResetLinkAction;
use App\DTOs\User\Signup\PasswordResetData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Signup\ForgotPasswordRequest;
use App\Http\Requests\User\Signup\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Forgot password (from the "Sign in" tab): email a reset link, then choose a new password on
 * the page the link opens.
 */
class PasswordResetController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('User/Signup/ForgotPassword', [
            'status' => session('status'),
            'sentTo' => session('sentTo'),
            'expireMinutes' => (int) config('auth.passwords.users.expire'),
            'urls' => ['send' => route('password.email'), 'login' => route('login')],
        ]);
    }

    public function store(ForgotPasswordRequest $request, SendPasswordResetLinkAction $sendLink): RedirectResponse
    {
        $email = $request->validated('email');
        $sendLink->execute($email);

        // Same answer for every address, so the page can't reveal who has an account.
        return back()
            ->with('status', 'If a LinkUp account uses that email, a reset link is on its way. Check your inbox and spam folder.')
            ->with('sentTo', $email);
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('User/Signup/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'token' => $token,
            'urls' => ['save' => route('password.store'), 'login' => route('login'), 'forgot' => route('password.request')],
        ]);
    }

    public function update(ResetPasswordRequest $request, ResetPasswordAction $reset): RedirectResponse
    {
        $reset->execute(PasswordResetData::fromValidated($request->validated()));

        return redirect()->route('login')->with('status', 'Your password has been changed. Sign in with your new password.');
    }
}
