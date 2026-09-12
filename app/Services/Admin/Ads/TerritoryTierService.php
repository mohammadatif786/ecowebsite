<?php

namespace App\Services\Admin\Ads;

use App\Models\TerritoryTier;
use Illuminate\Database\Eloquent\Collection;

class TerritoryTierService
{
    public function getAllTiers(): Collection
    {
        return TerritoryTier::orderBy('id')->get();
    }

    public function createTier(array $data): TerritoryTier
    {
        return TerritoryTier::create($data);
    }

    public function updateTier(TerritoryTier $tier, array $data): TerritoryTier
    {
        $tier->update($data);

        return $tier->fresh();
    }

    public function deleteTier(TerritoryTier $tier): void
    {
        $tier->delete();
    }
}
