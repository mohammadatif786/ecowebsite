<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\ChangeUnverifiedEmailAction;
use App\Actions\User\Signup\ResolveOnboardingStateAction;
use App\Actions\User\Signup\SendEmailVerificationCodeAction;
use App\Actions\User\Signup\VerifyEmailCodeAction;
use App\DTOs\User\Signup\ChangeEmailData;
use App\DTOs\User\Signup\EmailCodeData;
use App\DTOs\User\Signup\OnboardingStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Signup\ChangeEmailRequest;
use App\Http\Requests\User\Signup\VerifyEmailCodeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Verify email page that follows "Create account". Thin on purpose: every rule lives in an Action.
 */
class EmailVerificationController extends Controller
{
    public function __construct(private readonly ResolveOnboardingStateAction $resolveState) {}

    public function show(Request $request, SendEmailVerificationCodeAction $sendCode): Response|RedirectResponse
    {
        $user = $request->user();
        $state = $this->resolveState->execute($user);

        // Already confirmed (or never needed): send them on to wherever they belong.
        if ($state->stage !== OnboardingStage::Email) {
            return redirect()->route($state->redirectRoute() ?? 'user.feed');
        }

        return Inertia::render('User/Signup/VerifyEmail', [
            'email' => $user->email,
            'codeLength' => config('signup.email_code.length'),
            'ttlMinutes' => config('signup.email_code.ttl_minutes'),
            'resendAvailableIn' => $sendCode->secondsUntilResend($user),
            'status' => session('status'),
            'urls' => [
                'verify' => route('verification.code'),
                'resend' => route('verification.send'),
                'change' => route('verification.change'),
                'logout' => route('logout'),
            ],
        ]);
    }

    public function verify(VerifyEmailCodeRequest $request, VerifyEmailCodeAction $action): RedirectResponse
    {
        $action->execute(EmailCodeData::fromValidated($request->user(), $request->validated()));

        // Email done: on to the wizard (its own gate decides if anything else is missing).
        return redirect()->route('frontend.users.wizard');
    }

    public function resend(Request $request, SendEmailVerificationCodeAction $action): RedirectResponse
    {
        $action->execute($request->user());

        return back()->with('status', 'A new code is on its way.');
    }

    public function change(ChangeEmailRequest $request, ChangeUnverifiedEmailAction $action): RedirectResponse
    {
        $action->execute(ChangeEmailData::fromValidated($request->user(), $request->validated()));

        return back()->with('status', 'We sent a new code to your new email.');
    }
}
