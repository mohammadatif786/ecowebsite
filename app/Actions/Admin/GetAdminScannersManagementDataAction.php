<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\ScannersManagementDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminScannersManagementDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): ScannersManagementDataDTO
    {
        return $this->service->getScannersManagementData();
    }
}
