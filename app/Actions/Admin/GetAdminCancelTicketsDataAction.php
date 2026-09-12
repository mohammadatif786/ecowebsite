<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\CancelTicketsDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminCancelTicketsDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): CancelTicketsDataDTO
    {
        return $this->service->getCancelTicketsData();
    }
}
