<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! $organizer) {
            return Inertia::render('organizer/complete_organizer');
        }

        $period = $request->query('period', 'hourly');
        $eventId = $request->query('event_id', 'all');

        $payload = $this->homeDashboardService->getDashboardPayload($organizer, $period, $eventId);

        return Inertia::render('organizer/Dashboard/Dashboard', $payload);
    }


    public function wallnessSpaDashboard(Request $request)
    {
        $userId = Auth::id();
        $organizer = OrganizerProfile::where('user_id', $userId)->first();

        if (! $organizer) {
            return Inertia::render('organizer/complete_organizer');
        }

        $period = $request->query('period', 'hourly');
        $spaId = $request->query('spa_id', 'all');

        $spaData = $this->wellnessDashboardService->getDashboardData($organizer, $period, $spaId);

        return Inertia::render('organizer/Dashboard/WallnessSpaDashboard', [
            'spaData' => $spaData,
        ]);
    }


    public function cookoutDashboard(Request $request)
    {
        $userId = Auth::id();
        $organizer = OrganizerProfile::where('user_id', $userId)->first();

        if (! $organizer) {
            return Inertia::render('organizer/complete_organizer');
        }

        $period = $request->query('period', 'hourly');
        $eventId = $request->query('event_id', 'all');

        $cookoutData = $this->cookoutDashboardService->getDashboardData($organizer, $period, $eventId);

        return Inertia::render('organizer/Dashboard/CookoutDashboard', [
            'cookoutData' => $cookoutData
        ]);
    }
}
