<?php

namespace App\Http\Middleware;

use App\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FeatureGateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature)
    {
        if (!app(FeatureGateService::class)->allows(auth()->user(), $feature)) {
            return redirect()->route('frontend.subscription.plans')
                ->with('error', 'Please upgrade your plan to access this feature.');
        }

        return $next($request);
    }
}
