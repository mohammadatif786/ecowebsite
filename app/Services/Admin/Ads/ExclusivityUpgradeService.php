<?php

namespace App\Services\Admin\Ads;

use App\Models\ExclusivityUpgrade;
use Illuminate\Database\Eloquent\Collection;

class ExclusivityUpgradeService
{
    public function getAllUpgrades(): Collection
    {
        return ExclusivityUpgrade::orderBy('id')->get();
    }

    public function createUpgrade(array $data): ExclusivityUpgrade
    {
        return ExclusivityUpgrade::create($data);
    }

    public function updateUpgrade(ExclusivityUpgrade $upgrade, array $data): ExclusivityUpgrade
    {
        $upgrade->update($data);

        return $upgrade->fresh();
    }

    public function deleteUpgrade(ExclusivityUpgrade $upgrade): void
    {
        $upgrade->delete();
    }
}
