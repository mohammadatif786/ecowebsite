<?php

namespace App\Actions\Admin\SubscriptionPlans;

use App\Contracts\SubscriptionPlanRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSubscriptionPlanAction
{
    public function __construct(private readonly SubscriptionPlanRepositoryInterface $repository) {}

    public function execute(int $gateId): bool
    {
        $gate = $this->repository->find($gateId);

        return DB::transaction(
            fn() => $this->repository->delete($gate)
        );
    }
}
