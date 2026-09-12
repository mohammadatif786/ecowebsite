<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Wallet\{
    MoneySentMail,
    MoneyReceivedMail,
    MoneyRequestReceivedMail,
    MoneyRequestSentMail,
    MoneyRequestAcceptedMail,
    MoneyRequestRejectedMail,
    MoneyRequestCancelledMail,
    CoinsSentMail,
    CoinsReceivedMail,
    WalletRechargedMail,
    CoinsRechargedMail
};

class WalletEmailService
{
    /**
     * Send email when money is sent
     */
    public function sendMoneySentEmail(User $sender, User $recipient, int $amount, ?string $note = null): void
    {
        try {
            Mail::to($sender->email)->send(new MoneySentMail($sender, $recipient, $amount, $note));
        } catch (\Exception $e) {
            Log::error('Failed to send money sent email', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money is received
     */
    public function sendMoneyReceivedEmail(User $sender, User $recipient, int $amount, ?string $note = null): void
    {
        try {
            Mail::to($recipient->email)->send(new MoneyReceivedMail($sender, $recipient, $amount, $recipient->balance('USD')->value->get(), $note));
        } catch (\Exception $e) {
            Log::error('Failed to send money received email', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money request is received
     */
    public function sendMoneyRequestReceivedEmail(User $requester, User $recipient, int $amount, ?string $note = null): void
    {
        try {
            Mail::to($recipient->email)->send(new MoneyRequestReceivedMail($requester, $recipient, $amount, $note));
        } catch (\Exception $e) {
            Log::error('Failed to send money request received email', [
                'requester_id' => $requester->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money request is sent
     */
    public function sendMoneyRequestSentEmail(User $requester, User $recipient, int $amount, ?string $note = null): void
    {
        try {
            Mail::to($requester->email)->send(new MoneyRequestSentMail($requester, $recipient, $amount, $note));
        } catch (\Exception $e) {
            Log::error('Failed to send money request sent email', [
                'requester_id' => $requester->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money request is accepted
     */
    public function sendMoneyRequestAcceptedEmail(User $payer, User $payee, int $amount): void
    {
        try {
            Mail::to($payee->email)->send(new MoneyRequestAcceptedMail($payer, $payee, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send money request accepted email', [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money request is rejected
     */
    public function sendMoneyRequestRejectedEmail(User $requester, User $recipient, int $amount): void
    {
        try {
            Mail::to($requester->email)->send(new MoneyRequestRejectedMail($requester, $recipient, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send money request rejected email', [
                'requester_id' => $requester->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when money request is cancelled
     */
    public function sendMoneyRequestCancelledEmail(User $requester, User $recipient, int $amount): void
    {
        try {
            Mail::to($requester->email)->send(new MoneyRequestCancelledMail($requester, $recipient, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send money request cancelled email', [
                'requester_id' => $requester->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when coins are sent
     */
    public function sendCoinsSentEmail(User $sender, User $recipient, int $amount): void
    {
        try {
            Mail::to($sender->email)->send(new CoinsSentMail($sender, $recipient, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send coins sent email', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when coins are received
     */
    public function sendCoinsReceivedEmail(User $sender, User $recipient, int $amount): void
    {
        try {
            Mail::to($recipient->email)->send(new CoinsReceivedMail($sender, $recipient, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send coins received email', [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when wallet is recharged
     */
    public function sendWalletRechargedEmail(User $user, int $amount): void
    {
        try {
            Mail::to($user->email)->send(new WalletRechargedMail($user, $amount));
        } catch (\Exception $e) {
            Log::error('Failed to send wallet recharged email', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email when coins are recharged
     */
    public function sendCoinsRechargedEmail(User $user, int $amount, int $coins): void
    {
        try {
            Mail::to($user->email)->send(new CoinsRechargedMail($user, $amount, $coins));
        } catch (\Exception $e) {
            Log::error('Failed to send coins recharged email', [
                'user_id' => $user->id,
                'amount' => $amount,
                'coins' => $coins,
                'error' => $e->getMessage()
            ]);
        }
    }
}
