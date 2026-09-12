<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\EventSponsorsDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminEventSponsorsDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): EventSponsorsDataDTO
    {
        return $this->service->getEventSponsorsData();
    }
}
