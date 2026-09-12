<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\WalletMissionControlDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminWalletMissionControlDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): WalletMissionControlDataDTO
    {
        return $this->service->getWalletMissionControlData();
    }
}
