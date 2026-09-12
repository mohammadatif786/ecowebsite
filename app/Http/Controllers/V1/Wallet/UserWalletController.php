<?php

namespace App\Http\Controllers\V1\Wallet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AsueAction;
use App\Actions\InitiateWalletRechargeAction;
use App\Actions\SimulateAsueAcceptAction;
use App\Actions\SimulateAsueCycleAction;
use App\DTOs\SimulateAsueAcceptDTO;
use App\DTOs\SimulateAsueCycleDTO;
use App\DTOs\WalletRechargeDTO;
use App\Http\Requests\AsueStoreRequest;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserBankAccount;
use App\Models\UserMoneyRequest;
use App\Models\UserWalletKyc;
use App\Models\Asue;
use App\Services\AsueService;
use App\Services\WalletEmailService;
use App\Helpers\UserBankAccountHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;


class UserWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $balance = $user->balance('USD')->value->get();

        $transactions = $user->transactions()
            ->latest()
            ->get();

        $runningBalance = $balance;

        $transactions = $transactions->map(function ($tx) use (&$runningBalance, $user) {
            $isPositive = $tx->to_id == $user->id;

            $tx->is_positive = $isPositive;

            $tx->running_balance = $runningBalance;

            if ($isPositive) {
                $runningBalance -= $tx->amount;
            } else {
                $runningBalance += $tx->amount;
            }

            if (!empty($tx->meta) && isset($tx->meta['recipient_name'])) {
                $tx->recipient = [
                    'name' => $tx->meta['recipient_name'],
                    'linkup_id' => $tx->meta['recipient_linkup_id'] ?? '',
                ];
            }
            return $tx;
        });

        $users = User::contactForWallet()->get();
        $contacts = UserContact::where('user_id', $user->id)->with('contactUser')->get();
        $sentRequests = UserMoneyRequest::with('recipient', 'requester')->where('requester_id', $user->id)->orWhere('recipient_id', $user->id)->latest()->get();
        $subscriptions = $user->subscribed()->where('stripe_status', 'complete')->latest()->get();

        $activeAsue = Asue::whereIn('status', ['active', 'ACTIVE', 'inviting', 'INVITING'])
            ->whereNotNull('asue_unique_code')
            ->whereHas('invitedUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['invitedUsers' => function ($q) {
                $q->orderBy('asue_invites.position', 'asc');
            }])->latest()->first();

        $kycStatus = UserWalletKyc::where('user_id', $user->id)->first()?->status ?? 'not_submitted';

        $bankAccounts = UserBankAccount::where('user_id', $user->id)->get();
        $paypalId = $bankAccounts->first()?->paypal_id ?? null;
        $banks = $bankAccounts->map(function ($account) {
            return [
                'bank_name' => UserBankAccountHelper::safeDecrypt(fn() => $account->bank_name),
                'account_number' => UserBankAccountHelper::safeDecrypt(fn() => $account->account_number),
                'routing_number' => UserBankAccountHelper::safeDecrypt(fn() => $account->routing_number),
                'account_type' => UserBankAccountHelper::safeDecrypt(fn() => $account->account_type),
                'is_default' => $account->is_default ?? false,
            ];
        })->filter(function ($bank) {
            return !empty($bank['bank_name']) || !empty($bank['account_number']) || !empty($bank['routing_number']);
        })->values()->toArray();

        $bankWithdrawals = \App\Models\BankWithdrawal::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'fee_percent' => $withdrawal->fee_percent,
                    'fee_amount' => $withdrawal->fee_amount,
                    'payout_amount' => $withdrawal->payout_amount,
                    'bank_name' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->bank_name),
                    'account_number' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->account_number),
                    'status' => $withdrawal->status,
                    'failure_reason' => $withdrawal->failure_reason,
                    'created_at' => $withdrawal->created_at->toISOString(),
                    'processed_at' => $withdrawal->processed_at?->toISOString(),
                ];
            });

        return response()->json([
            'currentBalance' => $balance,
            'transactions' => $transactions,
            'users' => $users,
            'contacts' => $contacts,
            'sentRequests' => $sentRequests,
            'coinBalance' => $user->coins ?? 0,
            'subscriptions' => $subscriptions,
            'activeAsue' => $activeAsue,
            'kycStatus' => $kycStatus,
            'bankDetails' => [
                'paypal_id' => $paypalId,
                'banks' => $banks,
            ],
            'bankWithdrawals' => $bankWithdrawals,
        ]);
    }

    public function addContact(Request $request)
    {
        $validated = $request->validate([
            'contact_user_id' => 'required|exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);

        $userId = (int) Auth::id();
        $contactUserId = (int) $validated['contact_user_id'];

        if ($userId === $contactUserId) {
            return response()->json(['success' => false, 'message' => 'You cannot add yourself to Quick Pay.'], 422);
        }

        $exists = UserContact::where('user_id', $userId)
            ->where('contact_user_id', $contactUserId)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Contact already exists in Quick Pay.'], 409);
        }

        $contact = UserContact::create([
            'user_id' => $userId,
            'contact_user_id' => $contactUserId,
            'name' => $validated['name'] ?? null,
        ]);

        return response()->json(['success' => true, 'contact' => $contact], 201);
    }

    public function sendMoney(Request $request, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipientId' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = (int) Auth::id();
        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        try {
            $sender = User::findOrFail($senderId);
            $recipient = User::findOrFail($recipientId);

            $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);
            $action->sendMoneyToUser($dto);

            $emailService->sendMoneySentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyReceivedEmail($sender, $recipient, $amount, $note);

            return response()->json(['success' => true, 'message' => 'Money sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function requestMoney(Request $request, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipientId' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = (int) Auth::id();
        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        $globalKey = "money_request:sender:{$senderId}";
        if (RateLimiter::tooManyAttempts($globalKey, 10)) {
            return response()->json(['success' => false, 'message' => 'Too many requests. Please try again later.'], 429);
        }
        RateLimiter::hit($globalKey, 60);

        $pairKey = "money_request:pair:{$senderId}:{$recipientId}";
        if (RateLimiter::tooManyAttempts($pairKey, 3)) {
            return response()->json(['success' => false, 'message' => 'Too many requests to this user. Please try again later.'], 429);
        }
        RateLimiter::hit($pairKey, 60);

        try {
            $sender = User::findOrFail($senderId);
            $recipient = User::findOrFail($recipientId);

            $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);
            $action->requestMoneyFromUser($dto);

            $emailService->sendMoneyRequestSentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyRequestReceivedEmail($sender, $recipient, $amount, $note);

            return response()->json(['success' => true, 'message' => 'Money request sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function payMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $payerId = (int) Auth::id();

        try {
            $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->findOrFail($id);
            $action->fulfillMoneyRequest($id, $payerId);

            $emailService->sendMoneyRequestAcceptedEmail($moneyRequest->recipient, $moneyRequest->requester, (int) $moneyRequest->amount);
            $emailService->sendMoneyReceivedEmail($moneyRequest->recipient, $moneyRequest->requester, (int) $moneyRequest->amount);

            return response()->json(['success' => true, 'message' => 'Requested money paid successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function rejectMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $recipientId = (int) Auth::id();

        try {
            $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->findOrFail($id);
            $action->rejectMoneyRequest($id, $recipientId);

            $emailService->sendMoneyRequestRejectedEmail($moneyRequest->requester, $moneyRequest->recipient, (int) $moneyRequest->amount);

            return response()->json(['success' => true, 'message' => 'Money request rejected successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function cancelMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $requesterId = (int) Auth::id();

        try {
            $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->findOrFail($id);
            $action->cancelMoneyRequest($id, $requesterId);

            $emailService->sendMoneyRequestCancelledEmail($moneyRequest->requester, $moneyRequest->recipient, (int) $moneyRequest->amount);

            return response()->json(['success' => true, 'message' => 'Money request cancelled successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function storeAsue(AsueStoreRequest $request, AsueAction $asueAction)
    {
        try {
            $asue = $asueAction->execute($request->validated());
            return response()->json(['success' => true, 'message' => 'Asue created successfully.', 'asue' => $asue], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function acceptAsueParticipation(Asue $asue, AsueService $asueService)
    {
        try {
            $result = $asueService->acceptParticipation($asue, Auth::id());
            return response()->json(['success' => true, 'message' => $result['message']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function simulateAsueCycle(Asue $asue, SimulateAsueCycleAction $action)
    {
        try {
            $dto = SimulateAsueCycleDTO::fromRequest($asue, Auth::id());
            $result = $action->execute($dto);
            return response()->json(['success' => true, 'message' => $result['message']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function simulateAsueAccept(Asue $asue, SimulateAsueAcceptAction $action)
    {
        try {
            $dto = SimulateAsueAcceptDTO::fromRequest($asue, Auth::id());
            $result = $action->execute($dto);
            return response()->json(['success' => true, 'message' => $result['message']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
