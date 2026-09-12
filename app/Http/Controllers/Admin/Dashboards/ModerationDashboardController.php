<?php

namespace App\Http\Controllers\Admin\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\FlaggedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModerationDashboardController extends Controller
{
    public function index()
    {
        $total_users = User::where('type', 'user')->count();
        $flaged_users = FlaggedUser::count();
        $resolved_cases = FlaggedUser::where('status', true)->count();
        $pending_cases = FlaggedUser::where('status', false)->count();

        return Inertia::render('admin/Dashboards/Moderation', [
            'total_users' => $total_users,
            'flaged_users' => $flaged_users,
            'resolved_cases' => $resolved_cases,
            'pending_cases' => $pending_cases,
            'ten_flagged_users' => $this->TenFlaggedUser(),

        ]);
    }

    public function TenFlaggedUser()
    {
        $flagged_users = FlaggedUser::orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        return $flagged_users;
    }
}
