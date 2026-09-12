<?php

namespace App\Services;

use App\Repositories\SellerEarningRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SellerEarningService
{
    public function __construct(
        private SellerEarningRepository $repo
    ) {}

    /**
     * Full earnings page data for a given period.
     *
     * @param  string  $period  'today' | '7d' | '30d' | '90d' | 'custom'
     */
    public function getEarningsData($user, string $period = '30d', ?string $customStart = null, ?string $customEnd = null): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($period, $customStart, $customEnd);

        $runningTotal     = $this->repo->getRunningTotal($user->id);
        $earningsByPeriod = $this->repo->getEarningsByPeriod($user->id, $startDate, $endDate);
        $earningsByProduct = $this->repo->getEarningsByProduct($user->id, $startDate, $endDate);

        // Period totals for UI
        $periodRevenue  = collect($earningsByPeriod)->sum('earnings');
        $periodOrders   = collect($earningsByPeriod)->sum('orders');
        $periodUnits    = collect($earningsByPeriod)->sum('units_sold');

        // Percent of total
        $earningsByProduct = collect($earningsByProduct)->map(function ($row) use ($periodRevenue) {
            $row = (array) $row;
            $row['percent_of_total'] = $periodRevenue > 0
                ? round(($row['revenue'] / $periodRevenue) * 100, 1)
                : 0;
            return $row;
        })->toArray();

        $walletBalance = $user->balance('USD')->value->get();

        return [
            'running_total'    => $runningTotal,
            'period'           => $period,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'period_revenue'   => round((float) $periodRevenue, 2),
            'period_orders'    => (int) $periodOrders,
            'period_units'     => (int) $periodUnits,
            'earnings_by_period'  => $earningsByPeriod,
            'earnings_by_product' => $earningsByProduct,
            'walletBalance' => $walletBalance,
        ];
    }

    private function resolveDateRange(string $period, ?string $customStart, ?string $customEnd): array
    {
        return match ($period) {
            'today'  => [now()->toDateString(), now()->toDateString()],
            '7d'     => [now()->subDays(6)->toDateString(), now()->toDateString()],
            '90d'    => [now()->subDays(89)->toDateString(), now()->toDateString()],
            'custom' => [$customStart ?? now()->subDays(29)->toDateString(), $customEnd ?? now()->toDateString()],
            default  => [now()->subDays(29)->toDateString(), now()->toDateString()], // 30d
        };
    }

    /**
     * Get available earnings for cash out (only delivered orders minus completed cash out requests)
     */
    public function getAvailableEarnings(int $userId): float
    {
        $deliveredEarnings = $this->repo->getAvailableEarnings($userId);
        $completedCashOuts = \App\Models\SellerCashOutRequest::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');

        return max(0, $deliveredEarnings - $completedCashOuts);
    }

    /**
     * Get pending earnings (orders not yet delivered)
     */
    public function getPendingEarnings(int $userId): float
    {
        return $this->repo->getPendingEarnings($userId);
    }

    /**
     * Get Sales Report data
     */
    public function getSalesReport(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->repo->getSalesReport($userId, $startDate, $endDate);
    }

    /**
     * Get Cash-Out Report data
     */
    public function getCashOutReport(int $userId): array
    {
        return $this->repo->getCashOutReport($userId);
    }

    /**
     * Get Wallet Funding Report data
     */
    public function getWalletFundingReport(int $userId): array
    {
        return $this->repo->getWalletFundingReport($userId);
    }

    /**
     * Get invoice data for a specific order
     */
    public function getInvoiceData(int $userId, int $orderId): ?array
    {
        $orderData = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('users as buyer', 'o.user_id', '=', 'buyer.id')
            ->join('users as seller', 'p.user_id', '=', 'seller.id')
            ->where('p.user_id', $userId)
            ->where('o.id', $orderId)
            ->whereNotIn('o.status', ['cancelled'])
            ->select([
                'o.id as order_id',
                'o.number as order_number',
                'o.created_at as order_date',
                'o.status as order_status',
                'o.total as order_total',
                'o.subtotal_amount',
                'o.tax_amount',
                'o.shipping_amount',
                'o.discount_amount',
                'o.fee_amount',
                'o.fee_label',
                'o.net_total',
                'seller.name as seller_name',
                'seller.email as seller_email',
                'buyer.name as buyer_name',
                'buyer.email as buyer_email',
                'buyer.phone as buyer_phone',
                'o.shipping_method',
                'o.street_address',
                'o.city',
                'o.state',
                'o.country',
                'o.zipcode',
            ])
            ->first();

        if (!$orderData) {
            return null;
        }

        $items = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('oi.order_id', $orderId)
            ->where('p.user_id', $userId)
            ->select([
                'p.name as product_name',
                'oi.qty as quantity',
                'oi.unit_price',
                'oi.sub_total',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'sub_total' => (float) $item->sub_total,
                ];
            })->toArray();

        return [
            'order_id' => $orderData->order_id,
            'order_number' => $orderData->order_number,
            'order_date' => $orderData->order_date,
            'order_status' => $orderData->order_status,
            'order_total' => (float) $orderData->order_total,
            'subtotal_amount' => (float) $orderData->subtotal_amount,
            'tax_amount' => (float) $orderData->tax_amount,
            'shipping_amount' => (float) $orderData->shipping_amount,
            'discount_amount' => (float) $orderData->discount_amount,
            'fee_amount' => (float) $orderData->fee_amount,
            'fee_label' => $orderData->fee_label,
            'net_total' => (float) $orderData->net_total,
            'seller_name' => $orderData->seller_name,
            'seller_email' => $orderData->seller_email,
            'buyer_name' => $orderData->buyer_name,
            'buyer_email' => $orderData->buyer_email,
            'buyer_phone' => $orderData->buyer_phone,
            'shipping_method' => $orderData->shipping_method,
            'delivery_address' => trim($orderData->street_address . ', ' . $orderData->city . ', ' . $orderData->state . ' ' . $orderData->zipcode . ', ' . $orderData->country, ', '),
            'items' => $items,
        ];
    }
}
