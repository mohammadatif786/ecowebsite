<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\UsersDashboardDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminUsersDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): UsersDashboardDataDTO
    {
        return $this->service->getUsersDashboardData();
    }
}
