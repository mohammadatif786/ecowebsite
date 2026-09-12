<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\EventsDashboardDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminEventsDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): EventsDashboardDataDTO
    {
        return $this->service->getEventsDashboardData();
    }
}
