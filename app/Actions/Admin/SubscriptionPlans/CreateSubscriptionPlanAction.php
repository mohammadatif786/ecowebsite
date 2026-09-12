<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\DTOs\SubscriptionPlanData;
use App\Models\SubscriptionPlan;
use App\Contracts\SubscriptionPlanRepositoryInterface;
use App\Services\Admin\StripePriceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

class CreateSubscriptionPlanAction
{
    public function __construct(
        private readonly SubscriptionPlanRepositoryInterface $repository,
        private readonly StripePriceService $stripePriceService,
    ) {}

    public function execute(SubscriptionPlanData $data): SubscriptionPlan
    {
        try {
            $stripePriceId = $this->stripePriceService->createPriceForProduct(
                $data->stripeProductId,
                $data->price,
                $data->billingCycle,
            );
        } catch (ApiErrorException $e) {
            Log::error('Stripe price creation failed', [
                'product' => $data->stripeProductId,
                'message' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Could not create the Stripe price for this plan. Check the Product ID and try again.',
                previous: $e
            );
        }

        return DB::transaction(fn() => $this->repository->create([
            ...$data->toModelAttributes(),
            'stripe_price_id' => $stripePriceId,
        ]));
    }
}
