<?php

namespace App\Http\Controllers\Admin\Shops;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Merchants;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index()
    {
        // Get all users with their wallet balances
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance' => $user->balance('USD')->value->get(),
            ];
        });

        // Get merchants with their statistics
        $merchants = Merchants::with('user')->get()->map(function ($merchant) {
            $user = $merchant->user;
            return [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'email' => $user?->email,
                'merchant_type' => $merchant->merchant_type,
                'balance' => $user->balance('USD')->value->get(),
                'is_active' => $merchant->is_active,
            ];
        });

        // Get recent transactions
        $transactions = Transaction::with(['from', 'to'])
            ->latest()
            ->limit(50)
            ->get();

        $totalEscrow = WithdrawRequest::whereIn('request_status', ['pending', 'approved'])->sum('amount');
        
        // Calculate buyer pool by iterating through users
        $buyerPool = 0;
        User::chunk(200, function ($users) use (&$buyerPool) {
            foreach ($users as $user) {
                $buyerPool += $user->balance('USD')->value->get();
            }
        });
        
        // Calculate seller pool by iterating through merchants
        $sellerPool = 0;
        Merchants::whereHas('user')->chunk(200, function ($merchants) use (&$sellerPool) {
            foreach ($merchants as $merchant) {
                $sellerPool += $merchant->user->balance('USD')->value->get();
            }
        });

        return Inertia::render('admin/shops/wallets/Index', [
            'users' => $users,
            'merchants' => $merchants,
            'transactions' => $transactions,
            'stats' => [
                'total_escrow' => $totalEscrow,
                'buyer_pool' => $buyerPool,
                'seller_pool' => $sellerPool,
            ]
        ]);
    }

    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'balance' => 'required|numeric|min:0'
        ]);

        $user->update([
            'balance' => $request->balance
        ]);

        return back()->with('success', 'Wallet balance updated successfully.');
    }
}
