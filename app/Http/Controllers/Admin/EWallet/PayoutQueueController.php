<?php

namespace App\Http\Controllers\Admin\EWallet;

use App\Domain\Wallet\Actions\SettleBankWithdrawalAction;
use App\Http\Controllers\Controller;
use App\Models\BankWithdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** E-Wallet → Payout Queue: approve or reject a member's bank withdrawal. */
class PayoutQueueController extends Controller
{
    public function approve(BankWithdrawal $withdrawal, SettleBankWithdrawalAction $settle): JsonResponse
    {
        $w = $settle->approve($withdrawal->id, (int) auth()->id());

        return response()->json(['success' => true, 'message' => 'Withdrawal approved. The member has been emailed.', 'status' => $w->status]);
    }

    public function reject(Request $request, BankWithdrawal $withdrawal, SettleBankWithdrawalAction $settle): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);
        $w = $settle->reject($withdrawal->id, (int) auth()->id(), $data['reason']);

        return response()->json(['success' => true, 'message' => 'Withdrawal rejected and ' . number_format((float) $w->amount, 2) . ' refunded to the member.', 'status' => $w->status]);
    }
}
