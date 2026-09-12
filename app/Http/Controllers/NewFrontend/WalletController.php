<?php

namespace App\Http\Controllers\NewFrontend;

use App\Actions\CompleteCoinRechargeAction;
use App\Actions\CompleteWalletRechargeAction;
use App\Actions\InitiateCoinRechargeAction;
use App\Actions\InitiateWalletRechargeAction;
use App\DTOs\CoinPaymentSuccessDTO;
use App\DTOs\CoinRechargeDTO;
use App\DTOs\WalletPaymentSuccessDTO;
use App\DTOs\WalletRechargeDTO;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserMoneyRequest;
use App\Services\WalletEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function addContact(Request $request)
    {
        $request->validate([
            'contact_user_id' => 'required|exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);

        $userId = (int) Auth::id();
        $contactUserId = (int) $request->contact_user_id;

        if ($userId === $contactUserId) {
            return back()->withErrors(['contact_user_id' => 'You cannot add yourself to Quick Pay.']);
        }

        $exists = UserContact::where('user_id', $userId)
            ->where('contact_user_id', $contactUserId)
            ->exists();

        if ($exists) {
            return back()->withErrors(['contact_user_id' => 'Contact already exists in Quick Pay.']);
        }

        UserContact::create([
            'user_id' => $userId,
            'contact_user_id' => $contactUserId,
            'name' => $request->name,
        ]);

        return back()->with('success', 'Contact added successfully.');
    }

    public function removeContact(UserContact $contact)
    {
        abort_unless((int) $contact->user_id === (int) Auth::id(), 404);

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact removed.',
        ]);
    }

    public function sendMoney(Request $request, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipientId' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = (int) Auth::id();
        if ($senderId <= 0) {
            return redirect()->route('login');
        }

        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        $sender = User::find($senderId);
        $recipient = User::find($recipientId);
        $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);

        try {
            $action->sendMoneyToUser($dto);

            $emailService->sendMoneySentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyReceivedEmail($sender, $recipient, $amount, $note);

            return back()->with('success', 'Money sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => 'Transaction failed: ' . $e->getMessage()]);
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
        if ($senderId <= 0) {
            return redirect()->route('login');
        }

        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        $globalKey = "money_request:sender:{$senderId}";
        if (RateLimiter::tooManyAttempts($globalKey, 10)) {
            $seconds = RateLimiter::availableIn($globalKey);
            return back()->withErrors(['transaction' => "Too many requests. Please try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($globalKey, 60);

        $pairKey = "money_request:pair:{$senderId}:{$recipientId}";
        if (RateLimiter::tooManyAttempts($pairKey, 3)) {
            $seconds = RateLimiter::availableIn($pairKey);
            return back()->withErrors(['transaction' => "You have requested money from this user too many times. Please try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($pairKey, 60);

        $sender = User::find($senderId);
        $recipient = User::find($recipientId);
        $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);

        try {
            $action->requestMoneyFromUser($dto);

            $emailService->sendMoneyRequestSentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyRequestReceivedEmail($sender, $recipient, $amount, $note);

            return back()->with('success', 'Money Request sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function payMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $payerId = (int) Auth::id();
        if ($payerId <= 0) {
            return redirect()->route('login');
        }

        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (! $moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            $action->fulfillMoneyRequest($id, $payerId);

            $emailService->sendMoneyRequestAcceptedEmail($moneyRequest->recipient, $moneyRequest->requester, (int) $moneyRequest->amount);
            $emailService->sendMoneyReceivedEmail($moneyRequest->recipient, $moneyRequest->requester, (int) $moneyRequest->amount);

            return back()->with('success', 'Requested money sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function rejectMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $recipientId = (int) Auth::id();
        if ($recipientId <= 0) {
            return redirect()->route('login');
        }

        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (! $moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            $action->rejectMoneyRequest($id, $recipientId);

            $emailService->sendMoneyRequestRejectedEmail($moneyRequest->requester, $moneyRequest->recipient, (int) $moneyRequest->amount);

            return back()->with('success', 'Money request rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function cancelMoneyRequest(int $id, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $requesterId = (int) Auth::id();
        if ($requesterId <= 0) {
            return redirect()->route('login');
        }

        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (! $moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            $action->cancelMoneyRequest($id, $requesterId);

            $emailService->sendMoneyRequestCancelledEmail($moneyRequest->requester, $moneyRequest->recipient, (int) $moneyRequest->amount);

            return back()->with('success', 'Money request cancelled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function submitAmountToWallet(Request $request, InitiateWalletRechargeAction $action)
    {
        $request->validate([
            'selectedAmount' => 'required|integer|min:10|max:1000',
        ]);

        $dto = new WalletRechargeDTO(
            (int) Auth::id(),
            (int) $request->selectedAmount,
            $request->redirect_to ?: route('new_frontend.wallet'),
            0,
            null,
            'new_frontend.wallet.payment_success'
        );

        return Inertia::location($action->execute($dto));
    }

    public function submitCoinToWallet(Request $request, InitiateCoinRechargeAction $action)
    {
        $request->validate([
            'selectedCoin' => 'required|integer|min:1|max:10000',
            'coinCount' => 'required|integer|min:1|max:10000',
        ]);

        $dto = new CoinRechargeDTO(
            (int) Auth::id(),
            (int) $request->selectedCoin,
            (int) $request->coinCount,
            $request->redirect_to ?: route('new_frontend.wallet'),
            'new_frontend.wallet.coin_payment_success'
        );

        return Inertia::location($action->execute($dto));
    }

    public function oneTimePaySuccess(Request $request, CompleteWalletRechargeAction $action, WalletEmailService $emailService)
    {
        try {
            $result = $action->execute(new WalletPaymentSuccessDTO($request->session_id));

            if ($result && isset($result['amount']) && isset($result['user_id'])) {
                $user = User::find($result['user_id']);
                if ($user) {
                    $emailService->sendWalletRechargedEmail($user, $result['amount']);
                }
            }

            if ($request->redirect_to) {
                return redirect($request->redirect_to)->with('success', 'Funds added successfully!');
            }

            return redirect()->route('new_frontend.wallet')->with('success', 'Funds added successfully!');
        } catch (\Exception $e) {
            logger('Stripe API Error: ' . $e->getMessage());
            return redirect('/subscription-error')->with('error', $e->getMessage());
        }
    }

    public function oneTimePayCoinSuccess(Request $request, CompleteCoinRechargeAction $action, WalletEmailService $emailService)
    {
        try {
            $userId = (int) Auth::id();

            if ($userId <= 0) {
                return redirect()->route('login');
            }

            $result = $action->execute(new CoinPaymentSuccessDTO($request->session_id, $userId, 0));

            if ($result && isset($result['amount']) && isset($result['coins']) && isset($result['user_id'])) {
                $user = User::find($result['user_id']);
                if ($user) {
                    $emailService->sendCoinsRechargedEmail($user, $result['amount'], $result['coins']);
                }
            }

            if ($request->redirect_to) {
                return redirect($request->redirect_to)->with('success', 'Coins added successfully!');
            }

            return redirect()->route('new_frontend.wallet')->with('success', 'Coins added successfully!');
        } catch (\Exception $e) {
            logger('Stripe API Error: ' . $e->getMessage());
            return redirect('/subscription-error')->with('error', $e->getMessage());
        }
    }
}
