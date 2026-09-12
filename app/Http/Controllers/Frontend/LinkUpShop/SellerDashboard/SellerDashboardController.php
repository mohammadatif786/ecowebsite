<?php

namespace App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard;

use App\Http\Controllers\Controller;
use App\Services\SellerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SellerDashboardController extends Controller
{
    public function __construct(
        private SellerDashboardService $service
    ) {}

    /**
     * Analytics dashboard — summary stats, sales trend, product performance.
     */
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 30, 90]) ? $days : 30;

        $data = $this->service->getDashboardData(Auth::id(), $days);
        
        // Get user's wallet balance using the HasBalance trait
        $user = Auth::user();
        // $walletBalance = $user->balance('USD')->value->get();
        $walletBalance = $user->balance('USD')->value->get();

        return Inertia::render('User/LinkUpShop/SellerDashboard/Index', [
            'summary'       => $data['summary'],
            'salesTrend'    => $data['salesTrend'],
            'topProducts'   => $data['topProducts'],
            'productPerf'   => $data['productPerf'],
            'days'          => $days,
            'walletBalance' => $walletBalance,
        ]);
    }
}
