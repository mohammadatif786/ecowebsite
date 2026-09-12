<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\AdIndustryData;
use App\Models\AdIndustry;
use App\Services\Admin\Ads\AdIndustryService;
use Illuminate\Database\Eloquent\Collection;

class AdIndustryAction
{
    public function __construct(
        private AdIndustryService $service
    ) {}

    public function list(): Collection
    {
        return $this->service->getAllIndustries();
    }

    public function execute(AdIndustryData $data): AdIndustry
    {
        return $this->service->createIndustry($data->toArray());
    }

    public function update(AdIndustry $industry, AdIndustryData $data): AdIndustry
    {
        return $this->service->updateIndustry($industry, $data->toArray());
    }

    public function delete(AdIndustry $industry): void
    {
        $this->service->deleteIndustry($industry);
    }
}
