<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\Ads\ExclusivityUpgradeData;
use App\Models\ExclusivityUpgrade;
use App\Services\Admin\Ads\ExclusivityUpgradeService;
use Illuminate\Database\Eloquent\Collection;

class ExclusivityUpgradeAction
{
    public function __construct(
        private ExclusivityUpgradeService $service
    ) {}

    public function list(): Collection
    {
        return $this->service->getAllUpgrades();
    }

    public function execute(ExclusivityUpgradeData $data): ExclusivityUpgrade
    {
        return $this->service->createUpgrade($data->toArray());
    }

    public function update(ExclusivityUpgrade $upgrade, ExclusivityUpgradeData $data): ExclusivityUpgrade
    {
        return $this->service->updateUpgrade($upgrade, $data->toArray());
    }

    public function delete(ExclusivityUpgrade $upgrade): void
    {
        $this->service->deleteUpgrade($upgrade);
    }
}
