<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Contracts\FeatureGateRepositoryInterface;
use App\DTOs\FeatureGateData;
use App\Models\FeatureGate;
use Illuminate\Support\Facades\DB;

class CreateFeatureGateAction
{
    public function __construct(private readonly FeatureGateRepositoryInterface $repository) {}

    public function execute(FeatureGateData $data): FeatureGate
    {
        return DB::transaction(
            fn() => $this->repository->create(
                $data->toModelAttributes(),
                $data->planIds,
            )
        );
    }
}
