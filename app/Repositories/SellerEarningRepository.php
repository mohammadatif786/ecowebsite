<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SellerEarningRepository
{
    /**
     * All-time running total earnings for the seller.
     * Earnings = SUM(order_items.sub_total) for seller's products in non-cancelled orders.
     */
    public function getRunningTotal(int $userId): float
    {
        return (float) DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->sum('oi.sub_total');
    }

    /**
     * Available earnings for cash out (only delivered orders).
     * Funds are available only when buyer has taken the goods (status = delivered or received_buyer).
     */
    public function getAvailableEarnings(int $userId): float
    {
        return (float) DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereIn('o.status', ['delivered', 'received_buyer'])
            ->sum('oi.sub_total');
    }

    /**
     * Pending earnings (orders not yet delivered).
     * Includes orders with status: pending, processing, shipped, paid, etc.
     */
    public function getPendingEarnings(int $userId): float
    {
        return (float) DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled', 'delivered', 'received_buyer'])
            ->sum('oi.sub_total');
    }

    /**
     * Earnings grouped by day for a given date range.
     */
    public function getEarningsByPeriod(int $userId, string $startDate, string $endDate): array
    {
        return DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->whereBetween(DB::raw('DATE(o.created_at)'), [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(o.created_at)'))
            ->orderBy(DB::raw('DATE(o.created_at)'), 'desc')
            ->select([
                DB::raw('DATE(o.created_at) as date'),
                DB::raw('SUM(oi.sub_total) as earnings'),
                DB::raw('COUNT(DISTINCT o.id) as orders'),
                DB::raw('SUM(oi.qty) as units_sold'),
            ])
            ->get()
            ->toArray();
    }

    /**
     * Earnings broken down per product for the seller.
     */
    public function getEarningsByProduct(int $userId, string $startDate, string $endDate): array
    {
        return DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->whereBetween(DB::raw('DATE(o.created_at)'), [$startDate, $endDate])
            ->groupBy('p.id', 'p.name', 'p.cover_image', 'p.price')
            ->orderByRaw('SUM(oi.sub_total) desc')
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                'p.cover_image',
                'p.price as unit_price',
                DB::raw('SUM(oi.sub_total) as revenue'),
                DB::raw('SUM(oi.qty) as units_sold'),
                DB::raw('COUNT(DISTINCT o.id) as order_count'),
            ])
            ->get()
            ->toArray();
    }

    /**
     * Top N earning products all-time.
     */
    public function getTopProducts(int $userId, int $limit = 5): array
    {
        return DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.user_id', $userId)
            ->whereNotIn('o.status', ['cancelled'])
            ->groupBy('p.id', 'p.name', 'p.cover_image')
            ->orderByRaw('SUM(oi.sub_total) desc')
            ->limit($limit)
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                'p.cover_image',
                DB::raw('SUM(oi.sub_total) as revenue'),
                DB::raw('SUM(oi.qty) as units_sold'),
                DB::raw('COUNT(DISTINCT o.id) as order_count'),
            ])
            ->get()
            ->toArray();
    }

    /**
     * Sales Report - detailed breakdown of all items sold
     */
    public function getSalesReport(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = \App\Models\Order::with(['items', 'customer'])
            ->whereHas('items', function ($q) use ($userId) {
                $q->whereHas('product', function ($pq) use ($userId) {
                    $pq->where('user_id', $userId);
                });
            })
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        $orders = $query->get();

        $report = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // Only include items that belong to this seller
                if ($item->product && $item->product->user_id == $userId) {
                    $report[] = [
                        'order_id' => $order->id,
                        'order_number' => $order->number,
                        'purchase_date' => $order->created_at,
                        'order_status' => $order->status,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product ? $item->product->name : 'Unknown',
                        'quantity' => (int) $item->qty,
                        'sale_price' => (float) $item->unit_price,
                        'total_amount' => (float) $item->sub_total,
                        'buyer_name' => $order->customer ? $order->customer->name : 'Unknown',
                        'buyer_email' => $order->customer ? $order->customer->email : 'Unknown',
                        'buyer_phone' => $order->customer ? $order->customer->phone : null,
                        'shipping_method' => $order->shipping_method,
                        'delivery_address' => trim($order->street_address . ', ' . $order->city . ', ' . $order->state . ' ' . $order->zipcode . ', ' . $order->country, ', '),
                    ];
                }
            }
        }

        return $report;
    }

    /**
     * Cash-Out Report - detailed breakdown of all seller withdrawals
     */
    public function getCashOutReport(int $userId): array
    {
        return DB::table('seller_cash_out_requests as sc')
            ->where('sc.user_id', $userId)
            ->select([
                'sc.id',
                'sc.amount',
                'sc.status',
                'sc.rejection_reason',
                'sc.created_at as requested_at',
                'sc.processed_at',
                DB::raw("'LinkUp Wallet' as destination"),
                DB::raw("CONCAT('CO-', sc.id) as transaction_id"),
            ])
            ->orderBy('sc.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'amount' => (float) $item->amount,
                    'status' => $item->status,
                    'rejection_reason' => $item->rejection_reason,
                    'requested_at' => $item->requested_at,
                    'processed_at' => $item->processed_at,
                    'destination' => $item->destination,
                    'transaction_id' => $item->transaction_id,
                ];
            })->toArray();
    }

    /**
     * Wallet Funding Report - tracking transfers to LinkUp Wallet
     */
    public function getWalletFundingReport(int $userId): array
    {
        // Get wallet balance from the wallet package
        $balance = DB::table('balances')
            ->where('payable_id', $userId)
            ->where('payable_type', 'App\Models\User')
            ->where('currency', 'USD')
            ->first();

        if (!$balance) {
            return [];
        }

        // Get transaction records from transactions table (O21 wallet package)
        $transactions = DB::table('transactions')
            ->where(function ($query) use ($balance) {
                $query->where(function ($q) use ($balance) {
                    $q->where('from_type', 'App\Models\User')
                        ->where('from_id', $balance->payable_id);
                })->orWhere(function ($q) use ($balance) {
                    $q->where('to_type', 'App\Models\User')
                        ->where('to_id', $balance->payable_id);
                });
            })
            ->where('currency', 'USD')
            ->where('archived', false)
            ->where('invisible', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tx) use ($balance) {
                // Determine transaction type (credit/debit)
                $type = 'credit';
                if ($tx->to_type === 'App\Models\User' && $tx->to_id == $balance->payable_id) {
                    $type = 'credit';
                } elseif ($tx->from_type === 'App\Models\User' && $tx->from_id == $balance->payable_id) {
                    $type = 'debit';
                }

                // Get balance before/after from meta if available
                $meta = json_decode($tx->meta, true) ?? [];
                $balanceBefore = (float) ($meta['balance_before'] ?? 0);
                $balanceAfter = (float) ($meta['balance_after'] ?? 0);

                // Map status to readable format
                $statusMap = [
                    'success' => 'completed',
                    'pending' => 'pending',
                    'on_hold' => 'on_hold',
                    'in_progress' => 'processing',
                    'awaiting_approval' => 'pending',
                ];
                $status = $statusMap[$tx->status] ?? $tx->status;

                return [
                    'id' => $tx->id,
                    'uuid' => $tx->uuid,
                    'type' => $type,
                    'amount' => (float) $tx->amount,
                    'received' => (float) $tx->received,
                    'commission' => (float) $tx->commission,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => $status,
                    'reference_id' => $tx->uuid ?? 'TX-' . $tx->id,
                    'processor_id' => $tx->processor_id,
                    'description' => $meta['description'] ?? ucfirst(str_replace('_', ' ', $tx->processor_id ?? 'transaction')),
                    'created_at' => $tx->created_at,
                ];
            })->toArray();

        return $transactions;
    }

}
