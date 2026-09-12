<?php

namespace App\Http\Middleware;

use App\Models\OrganizerProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();

        if (!$organizer) {
           return Inertia::render('organizer/complete_organizer')->toResponse($request);
        }

        // Otherwise continue
        return $next($request);
    }
}
