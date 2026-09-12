<?php

namespace App\Services\Admin\Ads;

use App\Models\CampaignType;
use Illuminate\Database\Eloquent\Collection;

class CampaignTypeService
{

    public function getAllCampaignTypes(): Collection
    {
        $data = CampaignType::all();

        return $data;
    }

    public function createCampaignType(array $data): CampaignType
    {
        return CampaignType::create([
            'icon' => $data['icon'],
            'name' => $data['name'],
            'duration' => $data['duration'],
            'priceMax' => $data['priceMax'],
            'priceMin' => $data['priceMin'],
            'basePrice' => $data['basePrice'],
            'reach' => $data['reach'],
            'taxRate' => $data['taxRate'],
            'description' => $data['description'],
            'is_enterprise' => $data['is_enterprise'] ?? false,
        ]);
    }

    public function updateCampaignType(CampaignType $campaignType, array $data): CampaignType
    {
        $campaignType->update([
            'icon' => $data['icon'],
            'name' => $data['name'],
            'duration' => $data['duration'],
            'priceMax' => $data['priceMax'],
            'priceMin' => $data['priceMin'],
            'basePrice' => $data['basePrice'],
            'reach' => $data['reach'],
            'taxRate' => $data['taxRate'],
            'description' => $data['description'],
            'is_enterprise' => $data['is_enterprise'] ?? false,
        ]);

        return $campaignType->fresh();
    }

    public function deleteCampaignType(CampaignType $campaignType): void
    {
        $campaignType->delete();
    }
}
