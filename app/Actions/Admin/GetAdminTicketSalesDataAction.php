<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\TicketSalesDataDTO;
use App\Services\Admin\AdminOverviewService;

class GetAdminTicketSalesDataAction
{
    public function __construct(
        private AdminOverviewService $service
    ) {}

    public function execute(): TicketSalesDataDTO
    {
        return $this->service->getTicketSalesData();
    }
}
