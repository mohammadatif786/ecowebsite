<?php

namespace App\Actions;

use App\DTOs\WalletRechargeDTO;
use App\Services\StripePaymentService;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMoneyRequest;
use Illuminate\Support\Facades\DB;

class InitiateWalletRechargeAction
{
    public function __construct(
        protected StripePaymentService $stripePaymentService
    ) {}

    public function execute(WalletRechargeDTO $dto): string
    {
        $session = $this->stripePaymentService->createCheckoutSession($dto);

        return $session->url;
    }

    public function sendMoneyToUser(WalletRechargeDTO $dto)
    {
        DB::transaction(function () use ($dto) {
            if ($dto->userId === $dto->recipientId) {
                throw new \RuntimeException('You cannot send money to yourself.');
            }

            $firstId = min($dto->userId, $dto->recipientId);
            $secondId = max($dto->userId, $dto->recipientId);

            $lockedUsers = User::whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sender = $lockedUsers->get($dto->userId);
            $recipient = $lockedUsers->get($dto->recipientId);

            if (!$sender || !$recipient) {
                throw new \RuntimeException('Invalid sender or recipient.');
            }

            $amount = $dto->amount;
            if ($amount <= 0) {
                throw new \RuntimeException('Invalid amount.');
            }

            $senderBalance = $sender->balance('USD')->value->get();
            if ($senderBalance < $amount) {
                throw new \RuntimeException('Insufficient balance.');
            }

            transfer($amount, 'USD')
                ->from($sender)
                ->to($recipient)
                ->meta([
                    'type' => 'p2p_transfer',
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'sender_linkup_id' => $sender->linkup_id,
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'recipient_linkup_id' => $recipient->linkup_id,
                    'note' => $dto->note ?? '',
                ])
                ->commit();

            Notification::create([
                'title'    => 'You received money!',
                'message'  => "{$sender->name} sent you {$amount} USD.",
                'send_by'  => $sender->id,
                'user_id'  => $recipient->id,
                'type'     => 'payment',
                'context'  => 'money_transfer',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => [
                    'amount'      => $amount,
                    'sender_id'   => $sender->id,
                    'sender_name' => $sender->name,
                    'note'        => $dto->note ?? null,
                ],
            ]);
        });
    }

    public function requestMoneyFromUser(WalletRechargeDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            if ($dto->userId === $dto->recipientId) {
                throw new \RuntimeException('You cannot request money from yourself.');
            }

            $firstId = min($dto->userId, $dto->recipientId);
            $secondId = max($dto->userId, $dto->recipientId);

            $lockedUsers = User::whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $requester = $lockedUsers->get($dto->userId);
            $recipient = $lockedUsers->get($dto->recipientId);

            if (!$requester || !$recipient) {
                throw new \RuntimeException('Invalid requester or recipient.');
            }

            $amount = $dto->amount;
            if ($amount <= 0) {
                throw new \RuntimeException('Invalid amount.');
            }

            $moneyRequest = UserMoneyRequest::create([
                'requester_id' => $requester->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'note' => $dto->note ?? null,
                'status' => 'pending',
            ]);

            Notification::create([
                'title'    => 'You received money request',
                'message'  => "{$requester->name} sent money request of {$amount} USD.",
                'send_by'  => $requester->id,
                'user_id'  => $recipient->id,
                'type'     => 'Money Request',
                'context'  => 'Money Request',
                'unread'   => true,
                'avatar'   => $requester->avatar ?? null,
                'metadata' => [
                    'request_id' => $moneyRequest->id,
                    'amount' => $amount,
                    'sender_id' => $requester->id,
                    'sender_name' => $requester->name,
                    'note' => $dto->note ?? null,
                ],
            ]);
        });
    }

    public function fulfillMoneyRequest(int $moneyRequestId, int $payerId): void
    {
        DB::transaction(function () use ($moneyRequestId, $payerId) {
            $moneyRequest = UserMoneyRequest::where('id', $moneyRequestId)->lockForUpdate()->firstOrFail();

            if ($moneyRequest->status !== 'pending') {
                throw new \RuntimeException('This money request has already been processed.');
            }

            if ((int) $moneyRequest->recipient_id !== (int) $payerId) {
                throw new \RuntimeException('Unauthorized action.');
            }

            $requesterId = (int) $moneyRequest->requester_id;
            $recipientId = (int) $moneyRequest->recipient_id;

            if ($requesterId === $recipientId) {
                throw new \RuntimeException('Invalid money request.');
            }

            $firstId = min($requesterId, $recipientId);
            $secondId = max($requesterId, $recipientId);

            $lockedUsers = User::whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $payer = $lockedUsers->get($recipientId);
            $payee = $lockedUsers->get($requesterId);

            if (!$payer || !$payee) {
                throw new \RuntimeException('Invalid payer or payee.');
            }

            $amount = (float) $moneyRequest->amount;
            if ($amount <= 0) {
                throw new \RuntimeException('Invalid amount.');
            }

            $payerBalance = $payer->balance('USD')->value->get();
            if ($payerBalance < $amount) {
                throw new \RuntimeException('Insufficient balance to send this request.');
            }

            transfer($amount, 'USD')
                ->from($payer)
                ->to($payee)
                ->meta([
                    'type' => 'money_request_payment',
                    'request_id' => $moneyRequest->id,
                    'sender_id' => $payer->id,
                    'sender_name' => $payer->name,
                    'sender_linkup_id' => $payer->linkup_id,
                    'recipient_id' => $payee->id,
                    'recipient_name' => $payee->name,
                    'recipient_linkup_id' => $payee->linkup_id,
                    'note' => $moneyRequest->note ?? '',
                ])
                ->commit();

            $moneyRequest->update(['status' => 'accepted']);

            Notification::create([
                'title'    => "{$payer->name} sent you the requested money!",
                'message'  => "{$payer->name} sent you {$amount} USD as per your request!",
                'send_by'  => $payer->id,
                'user_id'  => $payee->id,
                'type'     => 'payment',
                'context'  => 'money_request_payment',
                'unread'   => true,
                'avatar'   => $payer->avatar ?? null,
                'metadata' => [
                    'request_id' => $moneyRequest->id,
                    'amount' => $amount,
                    'sender_id' => $payer->id,
                    'sender_name' => $payer->name,
                ],
            ]);
        });
    }

    public function rejectMoneyRequest(int $moneyRequestId, int $recipientId): void
    {
        DB::transaction(function () use ($moneyRequestId, $recipientId) {
            $moneyRequest = UserMoneyRequest::where('id', $moneyRequestId)->lockForUpdate()->firstOrFail();

            if ((int) $moneyRequest->recipient_id !== (int) $recipientId) {
                throw new \RuntimeException('Unauthorized action.');
            }

            if ($moneyRequest->status !== 'pending') {
                throw new \RuntimeException('This money request has already been processed.');
            }

            $moneyRequest->update(['status' => 'rejected']);
        });
    }

    public function cancelMoneyRequest(int $moneyRequestId, int $requesterId): void
    {
        DB::transaction(function () use ($moneyRequestId, $requesterId) {
            $moneyRequest = UserMoneyRequest::where('id', $moneyRequestId)->lockForUpdate()->firstOrFail();

            if ((int) $moneyRequest->requester_id !== (int) $requesterId) {
                throw new \RuntimeException('Unauthorized action.');
            }

            if ($moneyRequest->status !== 'pending') {
                throw new \RuntimeException('This money request can no longer be cancelled.');
            }

            $moneyRequest->delete();
        });
    }
}
