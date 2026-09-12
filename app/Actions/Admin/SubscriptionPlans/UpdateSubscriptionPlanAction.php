<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\DTOs\SubscriptionPlanData;
use App\Models\SubscriptionPlan;
use App\Contracts\SubscriptionPlanRepositoryInterface;
use App\Services\Admin\StripePriceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class UpdateSubscriptionPlanAction
{
    public function __construct(
        private readonly SubscriptionPlanRepositoryInterface $repository,
        private readonly StripePriceService $stripePriceService,
    ) {}

    public function execute(int $planId, SubscriptionPlanData $data): SubscriptionPlan
    {
        $plan = $this->repository->find($planId);

        $priceChanged = $plan->price != $data->price
            || $plan->billing_cycle !== $data->billingCycle
            || $plan->stripe_product_id !== $data->stripeProductId;

        $stripePriceId = $plan->stripe_price_id;

        if ($priceChanged) {
            try {
                $stripePriceId = $this->stripePriceService->createPriceForProduct(
                    $data->stripeProductId,
                    $data->price,
                    $data->billingCycle,
                );
            } catch (ApiErrorException $e) {
                // Don't block saving the plan's other fields if Stripe sync fails here;
                // the existing stripe_price_id is kept and the issue is only logged.
                Log::error('Stripe price update failed', ['error' => $e->getMessage()]);
            }
        }

        return DB::transaction(fn() => $this->repository->update($plan, [
            ...$data->toModelAttributes(),
            'stripe_price_id' => $stripePriceId,
        ]));
    }
}
