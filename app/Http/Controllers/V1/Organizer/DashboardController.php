<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventDetails;
use App\Models\OrganizerProfile;
use App\Models\LinkUpEvent;
use App\Models\ScanSignUser;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\TicketCheckin;
use App\Models\Payout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\Organizer\CookoutDashboardService;
use App\Services\Organizer\HomeDashboardService;
use App\Services\Organizer\WellnessDashboardService;

class DashboardController extends Controller
{

    protected $homeDashboardService;
    protected $cookoutDashboardService;
    protected $wellnessDashboardService;

    public function __construct(HomeDashboardService $homeDashboardService, CookoutDashboardService $cookoutDashboardService, WellnessDashboardService $wellnessDashboardService)
    {
        $this->homeDashboardService = $homeDashboardService;
        $this->cookoutDashboardService = $cookoutDashboardService;
        $this->wellnessDashboardService = $wellnessDashboardService;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $organizer = OrganizerProfile::select('id', 'organizer_name', 'user_id')
            ->where('user_id', $userId)
            ->first();

        if (!$organizer) {
            return redirect()->route('organizer.setup');
        }

        $period = $request->query('period', 'hourly');
        $eventId = $request->query('event_id', 'all');

        $payload = $this->homeDashboardService->getDashboardPayload($organizer, $period, $eventId);

        return response()->json($payload);
    }


    public function wallnessSpaDashboard(Request $request)
    {
        $userId = Auth::id();
        $organizer = OrganizerProfile::where('user_id', $userId)->first();

        if (!$organizer) {
            return redirect()->route('organizer.setup');
        }

        $period = $request->query('period', 'hourly');
        $spaId = $request->query('spa_id', 'all');

        $spaData = $this->wellnessDashboardService->getDashboardData($organizer, $period, $spaId);

        return response()->json($spaData);
    }


    public function cookoutDashboard(Request $request)
    {
        $userId = Auth::id();
        $organizer = OrganizerProfile::where('user_id', $userId)->first();

        if (!$organizer) {
            return redirect()->route('organizer.setup');
        }

        $period = $request->query('period', 'hourly');
        $eventId = $request->query('event_id', 'all');

        $cookoutData = $this->cookoutDashboardService->getDashboardData($organizer, $period, $eventId);

        return response()->json($cookoutData);
    }

}
