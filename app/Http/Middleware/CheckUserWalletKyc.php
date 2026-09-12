<?php

namespace App\Http\Middleware;

use App\Models\UserWalletKyc;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckUserWalletKyc
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $kyc = UserWalletKyc::where('user_id', $user->id)->first();

        // If it's the main wallet page or the kyc submission routes, allow access
        // so they can see the overlay and submit data.
        $allowedRoutes = [
            'frontend.user.wallet',
            'frontend.user.wallet.kyc',
            'frontend.user.wallet.kyc.store',
        ];

        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // For other routes, block if KYC is not approved
        if (!$kyc || $kyc->status !== 'approved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'KYC verification required.'], 403);
            }
            
            return redirect()->route('frontend.user.wallet')
                ->with('error', 'Please complete your KYC verification to access this feature.');
        }

        return $next($request);
    }
}
