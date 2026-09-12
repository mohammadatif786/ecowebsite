<?php

namespace App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard;

use App\Actions\UpdateOrderStatusAction;
use App\Http\Controllers\Controller;
use App\Services\SellerOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SellerOrderController extends Controller
{
    public function __construct(
        private SellerOrderService      $service,
        private UpdateOrderStatusAction $updateStatusAction,
    ) {}

    /**
     * Paginated orders list.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $orders  = $this->service->getPagedOrders(Auth::id(), $filters);

        return Inertia::render('User/LinkUpShop/SellerDashboard/Order', [
            'orders'  => $orders,
            'filters' => $filters,
        ]);
    }

    /**
     * Single order detail with buyer info — returns JSON for modal.
     */
    public function show(int $order)
    {
        $orderDetail = $this->service->getOrderDetail($order, Auth::id());

        return response()->json($orderDetail);
    }

    /**
     * Update order status.
     */
    public function update(Request $request, int $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $updated = $this->updateStatusAction->execute($order, Auth::id(), $request->status);

        return back()->with('success', "Order #{$updated->number} status updated to {$updated->status}.");
    }
}
