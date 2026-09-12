<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdTrackingController extends Controller
{
    /**
     * Record a swipe-ad interaction event.
     *
     * Expected JSON body:
     *   { "event_type": "impression" | "click" | "swipe_left" }
     *
     * Returns 204 No Content on success so the frontend fire-and-forget
     * approach works cleanly.
     */
    public function trackEvent(Request $request, Advertisement $advertisement): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'event_type' => ['required', 'in:impression,click,swipe_left'],
        ]);

        // Only track active ads – silently ignore if somehow an inactive ad
        // gets a tracking ping (e.g. race-condition between page load & status change).
        if (! $advertisement->status) {
            return response()->json(['ok' => false, 'reason' => 'inactive'], 200);
        }

        $advertisement->swipeAdEvents()->create([
            'user_id'    => Auth::id(),
            'event_type' => $request->event_type,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['ok' => true], 200);
    }
}
