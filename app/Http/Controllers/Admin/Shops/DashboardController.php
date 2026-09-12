<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\DTOs\ShopFeeData;

class DashboardController extends Controller
{
    public function index()
    {
        $total_orders = Order::count();
        $units_sold = OrderItem::count();
        $gross_revenue = Order::sum('total');
        $avg_order_value = Order::avg('total');
        $tax_total = Order::sum('tax_amount');
        $shipping_collected = Order::sum('shipping_amount');
        $discount_givent = Order::sum('discount_amount');
        $net_revenue = Order::sum('net_total');

        $escrow_pending = WithdrawRequest::where('request_status', 'pending')->sum('amount');
        $escrow_released = WithdrawRequest::where('request_status', 'approved')->sum('amount');

        $buyer_balance = $escrow_pending;

        return Inertia::render('admin/shops/dashboard/Index', [
            'total_orders' => $total_orders,
            'units_sold' => $units_sold,
            'gross_revenue' => $gross_revenue,
            'avg_order_value' => $avg_order_value,
            'tax_total' => $tax_total,
            'shipping_collected' => $shipping_collected,
            'discount_givent' => $discount_givent,
            'net_revenue' => $net_revenue,
            'escrow_pending' => $escrow_pending,
            'escrow_released' => $escrow_released,
            'buyer_balance' => $buyer_balance,
            'revenue_by_category' => $this->RevenueByCategory(),
            'category_breakdown' => $this->CategoryBreakDown(),
            'daily_summary' => $this->DailySummary(),
            'latest_orders' => $this->getLatestOrders(),
        ]);
    }

    public function getLatestOrders()
    {
        return Order::with(['user', 'items', 'trackings'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $latestTracking = $order->trackings->sortByDesc('created_at')->first();
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'customer_name' => $order->user?->name ?? 'Guest',
                    'customer_email' => $order->user?->email ?? 'N/A',
                    'total' => $order->total,
                    'currency' => 'USD',
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at->toDateTimeString(),
                    'items_count' => $order->items->count(),
                    'latest_tracking' => $latestTracking ? [
                        'status' => $latestTracking->status,
                        'note' => $latestTracking->note,
                        'date' => $latestTracking->created_at?->toDateTimeString(),
                    ] : null,
                ];
            });
    }

    public function RevenueByCategory()
    {
        $order_items = OrderItem::select('product_id', DB::raw('SUM(sub_total) as revenue'))
            ->groupBy('product_id')
            ->get()
            ->map(function ($query) {
                $category = ProductCategory::find($query->product_id);
                $query->name = $category->name ?? "";
                return $query;
            });

        return $order_items;
    }

    public function CategoryBreakDown()
    {
        $order_items = OrderItem::select('product_id', DB::raw('SUM(sub_total) as revenue'), DB::raw('COUNT(product_id) as units'))
            ->groupBy('product_id')
            ->get()
            ->map(function ($query) {
                $category = ProductCategory::find($query->product_id);
                $query->name = $category->name ?? "";
                return $query;
            });

        return $order_items;
    }

    public function DailySummary()
    {
        $order = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(subtotal_amount) as gross'),
            DB::raw('SUM(tax_amount) as tax'),
            DB::raw('SUM(shipping_amount) as ship'),
            DB::raw('SUM(discount_amount) as discount'),
            DB::raw('count(created_at) as orders')
        )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        return $order;
    }

    public function fees(\App\Repositories\ShopFeeRepository $repository)
    {
        return Inertia::render('admin/shops/fee/Index', [
            'fee' => $repository->getFee()
        ]);
    }

    public function storeFees(Request $request, \App\Actions\UpdateShopFeeAction $action)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'feeType' => 'required|string|in:percent,fixed,both',
            'label' => 'required|string|max:255',
            'percent' => 'nullable|numeric|min:0',
            'fixed' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'minFee' => 'nullable|numeric|min:0',
            'maxFee' => 'nullable|numeric|min:0',
            'disclaimer' => 'nullable|string',
        ]);

        $dto = ShopFeeData::fromRequest($request->all());
        $action->execute($dto);

        return back()->with('success', 'Fee settings updated successfully.');
    }
}
