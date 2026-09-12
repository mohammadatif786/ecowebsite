<?php

namespace App\Actions;

use App\DTOs\SubscriptionSuccessData;
use App\Services\StripeCheckoutService;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

class HandleSubscriptionSuccessAction
{
    public function __construct(
        private readonly StripeCheckoutService $stripeService,
    ) {}

    public function execute(SubscriptionSuccessData $data): Subscription
    {
        $session  = $this->stripeService->retrieveCheckoutSession($data->sessionId);
        $lineItem = $session->line_items->data[0];
        $amount   = $lineItem->price->unit_amount / 100;

        $stripeSub     = $session->subscription;
        $paymentStatus = $session->payment_status;

        $planId = $stripeSub->metadata->plan_id ?? $data->planId;

        if (!$planId) {
            throw new \RuntimeException('plan_id missing from session metadata and request.');
        }

        $subscription = Subscription::where('stripe_id', $stripeSub->id)->first();

        return DB::transaction(function () use ($subscription, $data, $stripeSub, $amount, $paymentStatus, $planId) {
            if ($subscription) {
                $subscription->forceFill([
                    'plan_id'       => (int) $planId,
                    'stripe_status' => $paymentStatus,
                    'stripe_price'  => $amount,
                ])->save();

                return $subscription;
            }

            $newSubscription = new Subscription();
            $newSubscription->forceFill([
                'user_id'       => $data->userId,
                'type'          => 'subscription',
                'quantity'      => 1,
                'stripe_id'     => $stripeSub->id,
                'stripe_price'  => $amount,
                'stripe_status' => $paymentStatus,
                'plan_id'       => (int) $planId,
                'ends_at'       => now()->addMonth(),
            ])->save();

            return $newSubscription;
        });
    }
}
