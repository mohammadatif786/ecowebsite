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
