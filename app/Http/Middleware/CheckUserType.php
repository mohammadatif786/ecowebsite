<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = Auth::user();

        // if not logged in, deny access
        if (!$user) {
            return redirect()->route($request->is('admin*') ? 'admin.login' : 'login');
        }

        // check if user's type is in allowed types
        if (! in_array($user->type, $types, true)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
