<?php

namespace App\Repositories;

use App\Models\FeatureGate;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\FeatureGateRepositoryInterface;


class FeatureGateRepository implements FeatureGateRepositoryInterface
{
    public function all(): Collection
    {
        return FeatureGate::with('plans')->latest()->get();
    }

    public function find(int $id): FeatureGate
    {
        return FeatureGate::with('plans')->findOrFail($id);
    }

    public function create(array $attributes, array $planIds): FeatureGate
    {
        $gate = FeatureGate::create($attributes);
        $gate->plans()->sync($planIds);

        return $gate->load('plans');
    }

    public function update(FeatureGate $gate, array $attributes, array $planIds): FeatureGate
    {
        $gate->update($attributes);
        $gate->plans()->sync($planIds);

        return $gate->refresh()->load('plans');
    }

    public function delete(FeatureGate $gate): bool
    {
        $gate->plans()->detach();

        return $gate->delete();
    }
}
