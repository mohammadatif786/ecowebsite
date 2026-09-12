<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shops\OrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\WithdrawRequest;
use App\Models\Merchants;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use O21\LaravelWallet\Models\Custodian;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginator = Order::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->with([
                'customer',
                'orderItems',
                'trackings' => function ($q) {
                    $q->latest('last_update')->limit(1);
                },
            ])
            ->paginate(10);

        return Inertia::render('admin/shops/order/Index', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'orderItems', 'orderItems.product', 'trackings' => function ($q) {
            $q->latest('last_update');
        }]);
        return Inertia::render('admin/shops/order/View', [
            'order' => $order
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->back()->withSuccess('Order Removed successfully');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:new,paid,processing,ready_for_pickup,picked_up,out_for_delivery,delivered,cancelled'
        ]);

        $status = $request->get('status');
        $order->update([
            'status' => $status
        ]);

        // Record Tracking
        $order->trackings()->create([
            'status' => $status,
            'last_update' => now(),
            'note' => 'Status updated by Administrator.'
        ]);

        return redirect()->back()->withSuccess('Status updated successfully');
    }

    public function escrowReleases(Request $request)
    {
        $paginator = Order::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->with(['customer', 'orderItems'])
            ->paginate(15);

        $totalEscrowVolume = Order::sum('total');
        
        $pendingEscrow = WithdrawRequest::where('request_status', 'pending')->sum('amount');
        
        $releasedEscrow = WithdrawRequest::where('request_status', 'approved')->sum('amount');

        return Inertia::render('admin/shops/escrow/EscrowReleases', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
            'stats' => [
                'total_volume' => $totalEscrowVolume,
                'pending_held' => $pendingEscrow,
                'released' => $releasedEscrow
            ]
        ]);
    }

    public function sellerTransfers(Request $request)
    {
        $paginator = WithdrawRequest::query()
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        $merchants = Merchants::query()
            ->where('is_active', true)
            ->get()
            ->map(function ($merchant) {
                $user = User::find($merchant->user_id);
                
                $total = OrderItem::whereHas('product', function($q) use ($merchant) {
                    $q->where('seller_owner', $merchant->id);
                })->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })->sum('sub_total');

                $totalRequested = WithdrawRequest::where('user_id', $merchant->user_id)->sum('amount');

                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'user_id' => $merchant->user_id,
                    'balance' => $user ? $user->balance : 0,
                    'type' => $merchant->merchant_type,
                    'total' => $total,
                    'total_requested' => $totalRequested
                ];
            });

        return Inertia::render('admin/shops/payouts/SellerTransfers', [
            'paginator' => $paginator,
            'merchants' => $merchants,
            'filters' => $request->only('search'),
        ]);
    }

    public function approveTransfer(WithdrawRequest $transfer)
    {
        $user = $transfer->user;
        if (!$user) {
            return redirect()->back()->withErrors('User not found.');
        }

        if ($transfer->order_id) {
            $order = $transfer->order;
            if ($order && $order->status !== 'received_buyer') {
                return redirect()->back()->withErrors('Order status must be "received_buyer" to approve transfer.');
            }
        }

        // Deposit to wallet (Transfer to wallet)
        deposit($transfer->amount, 'USD')
            ->from(Custodian::of('e_money'))
            ->to($user)
            ->overcharge()
            ->commit();

        $transfer->update([
            'request_status' => 'approved',
            'payment_status' => 'paid',
        ]);

        return redirect()->back()->withSuccess('Transfer approved successfully.');
    }

    public function rejectTransfer(WithdrawRequest $transfer)
    {
        $transfer->update([
            'request_status' => 'rejected',
        ]);

        return redirect()->back()->withSuccess('Transfer rejected.');
    }

    public function simulateTransfer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        WithdrawRequest::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'note' => $request->note ?? 'Simulated transfer request from Admin Panel',
            'request_status' => 'pending',
        ]);

        return redirect()->back()->withSuccess('Simulated transfer request created.');
    }
}
