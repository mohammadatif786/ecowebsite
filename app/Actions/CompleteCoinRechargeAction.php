<?php

namespace App\Actions;

use App\DTOs\CoinPaymentSuccessDTO;
use App\Services\StripePaymentService;
use App\Repositories\TransactionRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompleteCoinRechargeAction
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected TransactionRepository $transactionRepository
    ) {}

    public function execute(CoinPaymentSuccessDTO $dto): void
    {
        $id = $dto->sessionId;

        // Support direct PaymentIntent IDs (pi_...) from mobile app PaymentSheet
        if (str_starts_with($id, 'pi_')) {
            $paymentIntent = $this->stripeService->retrievePaymentIntent($id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('Payment not completed.');
            }

            $secureCoinCount = (int) ($paymentIntent->metadata->coin_count ?? request('coinCount') ?? request('coin_count') ?? request('coins') ?? 0);

            if ($secureCoinCount <= 0) {
                throw new \Exception('Invalid coin amount.');
            }

            if ($this->transactionRepository->isPaymentProcessed($paymentIntent->id)) {
                return;
            }

            DB::transaction(function () use ($dto, $paymentIntent, $secureCoinCount) {
                $this->transactionRepository->createSubscription([
                    'user_id'       => $dto->userId,
                    'type'          => 'coin',
                    'quantity'      => $secureCoinCount,
                    'stripe_id'     => $paymentIntent->id,
                    'stripe_price'  => $paymentIntent->amount / 100,
                    'stripe_status' => 'complete',
                    'ends_at'       => now(),
                ]);

                $user = User::lockForUpdate()->findOrFail($dto->userId);
                $user->increment('coins', $secureCoinCount);
            });

            return;
        }

        // Standard Stripe Checkout Session (cs_...)
        $session = $this->stripeService->retrieveSession($dto->sessionId);

        if ($session->payment_status !== 'paid') {
            throw new \Exception('Payment not completed.');
        }

        if ($session->client_reference_id != $dto->userId) {
            throw new \Exception('Security Error: User Mismatch.');
        }

        $secureCoinCount = (int) ($session->metadata->coin_count ?? 0);

        if ($secureCoinCount <= 0) {
            throw new \Exception('Invalid coin amount in session metadata.');
        }

        if ($this->transactionRepository->isPaymentProcessed($session->payment_intent)) {
            return;
        }

        DB::transaction(function () use ($dto, $session, $secureCoinCount) {
            $this->transactionRepository->createSubscription([
                'user_id'       => $dto->userId,
                'type'          => 'coin',
                'quantity'      => $secureCoinCount,
                'stripe_id'     => $session->payment_intent,
                'stripe_price'  => $session->amount_total / 100,
                'stripe_status' => $session->status,
                'ends_at'       => now(),
            ]);

            $user = User::lockForUpdate()->findOrFail($dto->userId);
            $user->increment('coins', $secureCoinCount);
        });
    }
}
