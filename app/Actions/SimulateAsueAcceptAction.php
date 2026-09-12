<?php

namespace App\Actions;

use App\DTOs\SimulateAsueAcceptDTO;
use App\Services\AsueService;

class SimulateAsueAcceptAction
{
    public function __construct(
        protected AsueService $asueService
    ) {}

    /**
     * Execute the Asue payout acceptance logic.
     *
     * @param SimulateAsueAcceptDTO $dto
     * @return array
     */
    public function execute(SimulateAsueAcceptDTO $dto): array
    {
        return $this->asueService->acceptPayout($dto);
    }
}
