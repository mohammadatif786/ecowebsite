<?php

namespace App\Http\Middleware;

use App\Actions\User\Signup\ResolveOnboardingStateAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias `isWizardComplete`: the gate in front of every member feature.
 *
 * A member gets through only when sign-up is finished: email confirmed (for accounts made by the
 * new sign-up) and the wizard completed. Anyone earlier in sign-up is sent back to the step
 * they are on. Organizers and admins keep their existing behaviour.
 */
class CheckWizardCompleted
{
    public function __construct(private readonly ResolveOnboardingStateAction $resolveState) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->type == 'user') {
            $state = $this->resolveState->execute($user);

            if ($state->isComplete()) {
                return $next($request);
            }

            // Stay in sign-up: the email page first, then the wizard.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Finish signing up to use this feature.',
                    'redirect' => route($state->redirectRoute()),
                ], 403);
            }

            return redirect()->route($state->redirectRoute());
        } elseif ($user->type == 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->type == 'organizer') {
            return $next($request); // Proceed to the next request
        }

        return redirect()->route('landing2');
    }
}
