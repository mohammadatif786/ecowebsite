<?php

namespace App\Contracts;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionPlanRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): SubscriptionPlan;
    public function create(array $attributes): SubscriptionPlan;
    public function update(SubscriptionPlan $plan, array $attributes): SubscriptionPlan;
    public function delete(SubscriptionPlan $plan): bool;
}
