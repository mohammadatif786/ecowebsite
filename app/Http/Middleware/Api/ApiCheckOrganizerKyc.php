<?php

namespace App\Http\Middleware\Api;

use App\Models\OrganizerKyc;
use App\Models\OrganizerProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiCheckOrganizerKyc
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
            return response()->json([
                'success' => false,
                'message' => 'Organizer profile not found. Please complete your organizer profile.'
            ], 403);
        }

        $kyc = OrganizerKyc::where('organizer_id', $organizerProfile->id)->first();

        if (!$kyc || $kyc->status === 'pending' || $kyc->status === 'canceled') {
            return response()->json([
                'success' => false,
                'message' => 'Organizer KYC not completed. Please complete your KYC.',
                'kyc_status' => $kyc ? $kyc->status : 'not_submitted'
            ], 403);
        }

        return $next($request);
    }
}
