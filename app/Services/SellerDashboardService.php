<?php

namespace App\Services;

use App\Repositories\SellerAnalyticsRepository;
use App\Repositories\SellerEarningRepository;

class SellerDashboardService
{
    public function __construct(
        private SellerAnalyticsRepository $analyticsRepo,
        private SellerEarningRepository   $earningRepo,
    ) {}

    /**
     * Main analytics dashboard data.
     */
    public function getDashboardData(int $userId, int $trendDays = 30): array
    {
        $summary         = $this->analyticsRepo->getSummaryStats($userId);
        $salesTrend      = $this->analyticsRepo->getSalesTrend($userId, $trendDays);
        $topProducts     = $this->earningRepo->getTopProducts($userId, 5);
        $productPerf     = $this->analyticsRepo->getProductPerformance($userId);

        return compact('summary', 'salesTrend', 'topProducts', 'productPerf');
    }
}
