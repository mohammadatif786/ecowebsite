<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\EventCouponsDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminEventCouponsDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): EventCouponsDataDTO
    {
        return $this->service->getEventCouponsData();
    }
}
