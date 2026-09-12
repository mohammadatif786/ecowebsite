<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // If regular user hits the home page, send them to their dashboard
        if ($routeName === 'user.home' && $user->type === 'user') {
            return redirect()->route('new_frontend.home');
        }

        // If admin hits the home page or admin home, send them to admin dashboard
        if (in_array($routeName, ['user.home', 'admin.home'], true) && $user->hasRole('admin')) {
            return redirect()->route('admin.new-dashboard');
        }

        return $next($request);
    }
}
