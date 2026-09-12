<?php

namespace App\Services\Admin\Ads;

use App\Models\AdIndustry;
use Illuminate\Database\Eloquent\Collection;

class AdIndustryService
{
    public function getAllIndustries(): Collection
    {
        return AdIndustry::orderBy('name')->get();
    }

    public function createIndustry(array $data): AdIndustry
    {
        return AdIndustry::create($data);
    }

    public function updateIndustry(AdIndustry $industry, array $data): AdIndustry
    {
        $industry->update($data);
        return $industry;
    }

    public function deleteIndustry(AdIndustry $industry): void
    {
        $industry->delete();
    }
}
