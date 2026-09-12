<?php

namespace App\Services\Admin\Ads;

use App\Models\AdSurgeOption;
use Illuminate\Database\Eloquent\Collection;

class AdSurgeOptionService
{
    public function getAllSurgeOptions(): Collection
    {
        return AdSurgeOption::all();
    }

    public function createSurgeOption(array $data): AdSurgeOption
    {
        return AdSurgeOption::create($data);
    }

    public function updateSurgeOption(AdSurgeOption $option, array $data): AdSurgeOption
    {
        $option->update($data);
        return $option;
    }

    public function deleteSurgeOption(AdSurgeOption $option): void
    {
        $option->delete();
    }
}
