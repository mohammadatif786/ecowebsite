<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SellerAnalyticsRepository
{
    /**
     * Summary stats for the analytics dashboard.
     */
    public function getSummaryStats(int $userId): array
    {
        // Revenue & order stats from order_items
        // Released revenue: only after buyer confirms receipt (status = received_buyer)
        // Pending revenue: orders that are not cancelled, not delivered, and not received_buyer
        $orderStats = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN o.status = 'received_buyer' THEN oi.sub_total ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN o.status NOT IN ('cancelled', 'delivered', 'received_buyer') THEN oi.sub_total ELSE 0 END), 0) as pending_revenue,
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(oi.qty), 0) as units_sold
            ")
            ->first();

        $totalProducts = DB::table('products')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->count();

        $avgOrderValue = $orderStats->total_orders > 0
            ? round((($orderStats->total_revenue + $orderStats->pending_revenue) / $orderStats->total_orders), 2)
            : 0;

        return [
            'total_revenue'   => (float) $orderStats->total_revenue,
            'pending_revenue' => (float) $orderStats->pending_revenue,
            'total_orders'    => (int)   $orderStats->total_orders,
            'total_products'  => (int)   $totalProducts,
            'units_sold'      => (int)   $orderStats->units_sold,
            'avg_order_value' => (float) $avgOrderValue,
        ];
    }

    /**
     * Daily sales trend for the past N days.
     */
    public function getSalesTrend(int $userId, int $days = 30): array
    {
        return DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->where('o.created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy(DB::raw('DATE(o.created_at)'))
            ->orderBy(DB::raw('DATE(o.created_at)'))
            ->select([
                DB::raw('DATE(o.created_at) as date'),
                DB::raw('COALESCE(SUM(oi.sub_total), 0) as revenue'),
                DB::raw('COUNT(DISTINCT o.id) as orders'),
            ])
            ->get()
            ->toArray();
    }

    /**
     * Product performance from product_analytics table.
     */
    public function getProductPerformance(int $userId): array
    {
        return DB::table('product_analytics as pa')
            ->join('products as p', 'pa.product_id', '=', 'p.id')
            ->where('pa.user_id', $userId)
            ->groupBy('pa.product_id', 'p.name', 'p.cover_image', 'p.price')
            ->orderByRaw('SUM(pa.revenue) desc')
            ->select([
                'pa.product_id',
                'p.name as product_name',
                'p.cover_image',
                'p.price as unit_price',
                DB::raw('SUM(pa.views) as total_views'),
                DB::raw('SUM(pa.clicks) as total_clicks'),
                DB::raw('SUM(pa.conversions) as total_conversions'),
                DB::raw('SUM(pa.revenue) as total_revenue'),
                DB::raw('ROUND(CASE WHEN SUM(pa.views) > 0 THEN (SUM(pa.conversions) / SUM(pa.views)) * 100 ELSE 0 END, 2) as conversion_rate'),
            ])
            ->get()
            ->toArray();
    }
}
