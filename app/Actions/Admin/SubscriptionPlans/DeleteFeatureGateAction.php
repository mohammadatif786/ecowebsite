<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Contracts\FeatureGateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFeatureGateAction
{
    public function __construct(private readonly FeatureGateRepositoryInterface $repository) {}

    public function execute(int $gateId): bool
    {
        $gate = $this->repository->find($gateId);

        return DB::transaction(
            fn() => $this->repository->delete($gate)
        );
    }
}
