<?php

namespace App\Repositories;

use App\Models\SubscriptionPlan;
use App\Contracts\SubscriptionPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;


class SubscriptionPlanRepository implements SubscriptionPlanRepositoryInterface
{
    public function all(): Collection
    {
        return SubscriptionPlan::query()->latest()->get();
    }

    public function find(int $id): SubscriptionPlan
    {
        return SubscriptionPlan::query()->findOrFail($id);
    }

    public function create(array $attributes): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create($attributes);
    }

    public function update(SubscriptionPlan $plan, array $attributes): SubscriptionPlan
    {
        $plan->update($attributes);

        return $plan->refresh();
    }

    public function delete(SubscriptionPlan $plan): bool
    {
        return $plan->delete();
    }
}
