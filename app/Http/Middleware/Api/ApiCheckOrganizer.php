<?php

namespace App\Http\Middleware\Api;

use App\Models\OrganizerProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiCheckOrganizer
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
            return response()->json([
                'message' => 'Organizer profile not found. Please complete your organizer profile.',
                'redirect' => route('organizer.profile.index')
            ], 403);
        }
        return $next($request);
    }
}
