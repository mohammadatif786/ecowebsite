<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\ContinueSignInAction;
use App\DTOs\User\Signup\AccountPageData;
use App\DTOs\User\Signup\SignInDestination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The "Sign in" tab. Members sign in with their LinkUp ID or email.
 * (The old phone-code screens for existing members stay in AuthenticatedSessionController.)
 */
class SignInController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('User/Signup/Account', AccountPageData::signIn()->toArray());
    }

    public function store(LoginRequest $request, ContinueSignInAction $continueSignIn): RedirectResponse
    {
        $request->authenticate();

        /** @var User $member */
        $member = Auth::user();
        $outcome = $continueSignIn->execute($member);

        if ($outcome->destination === SignInDestination::PhoneCode) {
            // Correct password, but the session must not stay signed in until the phone code is
            // confirmed. Otherwise the step could be skipped by letting the code expire.
            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->put('otp_remember', $request->boolean('remember'));

            return redirect()->route('verify.otp', ['token' => $outcome->phoneCodeToken]);
        }

        $request->session()->regenerate();

        return match ($outcome->destination) {
            SignInDestination::VerifyEmail => redirect()->route('verification.notice'),
            default => redirect()->intended(route('user.feed', absolute: false)),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        $member = Auth::user();

        if ($member instanceof User) {
            $member->update([
                'is_active' => 0,
                'last_active' => now(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/register');
    }
}
