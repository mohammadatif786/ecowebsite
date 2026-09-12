<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\TerritoryTierData;
use App\Models\TerritoryTier;
use App\Services\Admin\Ads\TerritoryTierService;
use Illuminate\Database\Eloquent\Collection;

class TerritoryTierAction
{
    public function __construct(
        private TerritoryTierService $service
    ) {}

    public function list(): Collection
    {
        return $this->service->getAllTiers();
    }

    public function execute(TerritoryTierData $data): TerritoryTier
    {
        return $this->service->createTier($data->toArray());
    }

    public function update(TerritoryTier $tier, TerritoryTierData $data): TerritoryTier
    {
        return $this->service->updateTier($tier, $data->toArray());
    }

    public function delete(TerritoryTier $tier): void
    {
        $this->service->deleteTier($tier);
    }
}
