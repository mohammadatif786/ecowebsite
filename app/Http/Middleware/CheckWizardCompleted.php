<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckWizardCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->type == 'user') {
            // Check if is_wizard_completed is true
            if (Auth::user()->is_wizard_completed) {
                return $next($request); // Proceed to the next request
            }
            // Redirect to wizard page if not completed
            return redirect()->route('frontend.users.wizard');
        } else if (Auth::user()->type == 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->type == 'organizer') {
            return $next($request); // Proceed to the next request

        }

        return redirect()->route('landing2');
    }
}
