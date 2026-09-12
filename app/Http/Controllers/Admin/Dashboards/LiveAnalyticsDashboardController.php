<?php

namespace App\Http\Controllers\Admin\Dashboards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveAnalyticsDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Dashboards/LiveAnalytics');
    }
}
