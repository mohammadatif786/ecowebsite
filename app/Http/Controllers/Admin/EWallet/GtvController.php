<?php

namespace App\Http\Controllers\Admin\EWallet;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminOverviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sidebar "Global GTV": money that actually moved through LinkUp — Stripe payments (top-ups, coins, plans),
 * ticket sales, marketplace orders and member-to-member wallet transfers. Cached for 5 minutes.
 */
class GtvController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $data = Cache::remember('admin:global-gtv', now()->addMinutes(5), function () {
            $sum = fn (string $table, string $column, ?callable $scope = null) => Schema::hasTable($table)
                ? (float) ($scope ? $scope(DB::table($table)) : DB::table($table))->sum(DB::raw("CAST({$column} AS DECIMAL(14,2))"))
                : 0.0;
            $types = AdminOverviewRepository::USER_TYPES;

            $parts = [
                'payments' => $sum('subscriptions', 'stripe_price'),
                'tickets' => $sum('ticket_sales', 'total'),
                'orders' => $sum('orders', 'total'),
                'transfers' => $sum('transactions', 'amount', fn ($q) => $q->where('status', 'success')
                    ->where('processor_id', 'transfer')->whereIn('from_type', $types)->whereIn('to_type', $types)),
            ];

            return ['total' => round(array_sum($parts), 2), 'parts' => array_map(fn ($v) => round($v, 2), $parts), 'at' => now()->toISOString()];
        });

        return response()->json($data);
    }
}
