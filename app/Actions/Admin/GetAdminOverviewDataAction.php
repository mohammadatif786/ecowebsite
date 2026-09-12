<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\AdminOverviewDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminOverviewDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): AdminOverviewDataDTO
    {
        return $this->service->getOverviewData();
    }
}
