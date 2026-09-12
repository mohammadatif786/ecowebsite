<?php

namespace App\Http\Middleware;

use App\Models\OrganizerKyc;
use App\Models\OrganizerProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizerKyc
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizerProfile = OrganizerProfile::where('user_id', Auth::id())->first();

        if (!$organizerProfile) {
            // no organizer profile → block
            return Inertia::render('organizer/KycIncomplete', [
                'kyc'    => null,
                'appURL' => asset('storage'),
            ])->toResponse($request);
        }

        $kyc = OrganizerKyc::where('organizer_id', $organizerProfile->id)->first();
        $appURL = env('APP_URL') . "storage/";

        if (!$kyc || $kyc->status === 'pending' || $kyc->status === 'canceled') {
            return Inertia::render('organizer/KycIncomplete', [
                'kyc'    => $kyc,
                'appURL' => $appURL,
            ])->toResponse($request);
        }

        return $next($request);
    }
}
