<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\AdSurgeOptionData;
use App\Models\AdSurgeOption;
use App\Services\Admin\Ads\AdSurgeOptionService;
use Illuminate\Database\Eloquent\Collection;

class AdSurgeOptionAction
{
    public function __construct(
        private AdSurgeOptionService $service
    ) {}

    public function list(): Collection
    {
        return $this->service->getAllSurgeOptions();
    }

    public function execute(AdSurgeOptionData $data): AdSurgeOption
    {
        return $this->service->createSurgeOption($data->toArray());
    }

    public function update(AdSurgeOption $option, AdSurgeOptionData $data): AdSurgeOption
    {
        return $this->service->updateSurgeOption($option, $data->toArray());
    }

    public function delete(AdSurgeOption $option): void
    {
        $this->service->deleteSurgeOption($option);
    }
}
