<?php

namespace App\Http\Controllers\Admin\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use App\Models\LinkUpEvent;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\UserMatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

class UserDashboardController extends Controller
{
    public function index()
    {
        $total_users = User::where('type', 'user')->count();
        $new_users = User::where('type', 'user')->whereDate('created_at', Carbon::today())->count();
        $total_events = LinkUpEvent::count();
        $total_organizers = EventOrganizer::count();
        $total_matches = UserMatch::where('status', 'like')->count();
        $subscription_revenue = Subscription::where('type', 'subscription')->sum('stripe_price');
        $event_revenue = TicketSale::sum('total');
        return Inertia::render('admin/Dashboards/Dashboard', [
            'total_users' => $total_users,
            'new_users' => $new_users,
            'total_events' => $total_events,
            'total_organizers' => $total_organizers,
            'total_matches' => $total_matches,
            'subscription_renvue' => $subscription_revenue,
            'event_revenue' => $event_revenue,
            'total_revenue' => (float) $subscription_revenue + (float) $event_revenue,
            'gender_graph' => $this->dountChart(),
            'top_users' => $this->topActiveUsers(),
            'matches_graph' => $this->MatchesLikesGraph(),

        ]);
    }

    public function dountChart()
    {
        $users = User::select('gender', DB::raw('COUNT(*) as total'))
            ->where('type', 'user')
            ->groupBy('gender')
            ->get();
        $male = 0;
        $female = 0;

        foreach ($users as $user) {
            if (strtolower($user->gender) === 'male') {
                $male = $user->total;
            } elseif (strtolower($user->gender) === 'female') {
                $female = $user->total;
            }
        }

        return [
            'male' => $male,
            'female' => $female,
        ];
    }

    public function topActiveUsers()
    {
        $top_users = User::query()
            ->select('users.id', 'users.name', 'users.country')
            // Matches sent (outgoing)
            ->withCount([
                'matchesSent as matches_count' => function ($query) {
                    $query->where('status', 'like');
                },
                // Likes received (incoming likes)
                'matchesReceived as likes_count' => function ($query) {
                    $query->where('status', 'like');
                },
            ])
            // Use the correct alias "coins_spent"
            ->orderByRaw('(matches_count + likes_count) DESC')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                $user->usd_balance = $user->balance('USD')->value->get();
                return $user;
            });

        return $top_users;
    }

    public function MatchesLikesGraph()
    {
        $data = DB::table('user_matches')
            ->selectRaw('DATE_FORMAT(created_at, "%b") as month')
            ->selectRaw('SUM(status = "like") as likes')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)')
            ->get();
        return $data;
    }
}
