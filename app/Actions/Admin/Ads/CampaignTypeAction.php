<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\CampaignTypeData;
use App\Models\CampaignType;
use App\Services\Admin\Ads\CampaignTypeService;
use Illuminate\Database\Eloquent\Collection;

class CampaignTypeAction
{
    public function __construct(
        private CampaignTypeService $service
    ) {}
    public function list(): Collection
    {
        return $this->service->getAllCampaignTypes();
    }
    public function execute(CampaignTypeData $data): CampaignType
    {
        return $this->service->createCampaignType($data->toArray());
    }

    public function update(CampaignType $campaignType, CampaignTypeData $data): CampaignType
    {
        return $this->service->updateCampaignType($campaignType, $data->toArray());
    }

    public function delete(CampaignType $campaignType): void
    {
        $this->service->deleteCampaignType($campaignType);
    }
}
