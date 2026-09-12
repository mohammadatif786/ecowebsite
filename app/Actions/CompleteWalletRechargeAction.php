<?php

namespace App\Actions;

use App\DTOs\WalletPaymentSuccessDTO;
use App\Services\StripePaymentService;
use App\Repositories\TransactionRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use O21\LaravelWallet\Models\Custodian;

class CompleteWalletRechargeAction
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected TransactionRepository $transactionRepository
    ) {}

    public function execute(WalletPaymentSuccessDTO $dto): void
    {
        $session = $this->stripeService->retrieveSession($dto->sessionId);

        if ($session->payment_status !== 'paid') {
            throw new \Exception('Payment verification failed: Status is ' . $session->payment_status);
        }

        $metadataType = (string) ($session->metadata->type ?? '');
        if ($metadataType !== 'wallet_recharge') {
            throw new \Exception('Payment verification failed: Invalid session type.');
        }

        $userId = (int) ($session->client_reference_id ?? 0);
        if ($userId <= 0) {
            throw new \Exception('Payment verification failed: Missing user reference.');
        }

        $metadataUserId = (int) ($session->metadata->user_id ?? 0);
        if ($metadataUserId > 0 && $metadataUserId !== $userId) {
            throw new \Exception('Security Error: User Mismatch.');
        }

        $amountCents = (int) ($session->amount_total ?? 0);
        if ($amountCents <= 0) {
            throw new \Exception('Payment verification failed: Invalid amount.');
        }

        $metadataAmountCents = (int) ($session->metadata->amount_cents ?? 0);
        if ($metadataAmountCents > 0 && $metadataAmountCents !== $amountCents) {
            throw new \Exception('Payment verification failed: Amount mismatch.');
        }

        if ($this->transactionRepository->isPaymentProcessed($session->payment_intent)) {
            return;
        }

        DB::transaction(function () use ($session, $userId, $amountCents) {

            $user = User::where('id', $userId)->lockForUpdate()->firstOrFail();

            $this->transactionRepository->createSubscription([
                'user_id' => $userId,
                'type' => 'wallet',
                'quantity' => 1,
                'stripe_id' => $session->payment_intent,
                'stripe_price' => $session->amount_total / 100,
                'stripe_status' => $session->status,
                'ends_at' => now(),
            ]);

            $actualAmount = $amountCents / 100;

            deposit($actualAmount, 'USD')
                ->from(Custodian::of('e_money'))
                ->to($user)
                ->overcharge()
                ->meta([
                    'stripe_intent' => $session->payment_intent,
                    'type' => 'recharge'
                ])
                ->commit();
        });
    }
}
