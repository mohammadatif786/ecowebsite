<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\OrganizerDirectoryDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminOrganizerDirectoryDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): OrganizerDirectoryDataDTO
    {
        return $this->service->getOrganizerDirectoryData();
    }
}
