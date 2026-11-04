<?php

namespace App\Http\Middleware;

use App\Actions\User\Signup\ResolveOnboardingStateAction;
use App\DTOs\User\Signup\OnboardingStage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias `signup.email`. Keeps a new member on the Verify email page until their email code is
 * entered. Used on the wizard and phone-code routes, which sit outside the `isWizardComplete`
 * group because an unfinished member has to reach them.
 */
class EnsureSignupEmailVerified
{
    public function __construct(private readonly ResolveOnboardingStateAction $resolveState) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($this->resolveState->execute($user)->stage !== OnboardingStage::Email) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Confirm your email to continue.',
                'redirect' => route('verification.notice'),
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
}
