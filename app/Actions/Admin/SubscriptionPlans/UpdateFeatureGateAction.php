<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Contracts\FeatureGateRepositoryInterface;
use App\DTOs\FeatureGateData;
use App\Models\FeatureGate;
use Illuminate\Support\Facades\DB;

class UpdateFeatureGateAction
{
    public function __construct(private readonly FeatureGateRepositoryInterface $repository) {}

    public function execute(int $gateId, FeatureGateData $data): FeatureGate
    {
        $gate = $this->repository->find($gateId);

        return DB::transaction(
            fn() => $this->repository->update(
                $gate,
                $data->toModelAttributes(),
                $data->planIds
            )
        );
    }
}
