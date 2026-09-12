<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\EventCategoriesDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminEventCategoriesDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): EventCategoriesDataDTO
    {
        return $this->service->getEventCategoriesData();
    }
}
