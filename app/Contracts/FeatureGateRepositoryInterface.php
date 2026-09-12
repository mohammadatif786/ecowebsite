<?php

namespace App\Contracts;

use App\Models\FeatureGate;
use Illuminate\Database\Eloquent\Collection;

interface FeatureGateRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): FeatureGate;
    public function create(array $attributes, array $planIds): FeatureGate;
    public function update(FeatureGate $gate, array $attributes, array $planIds): FeatureGate;
    public function delete(FeatureGate $gate): bool;
}
