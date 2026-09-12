<?php

namespace App\Actions;

use App\DTOs\SimulateAsueCycleDTO;
use App\Services\AsueService;

class SimulateAsueCycleAction
{
    public function __construct(
        protected AsueService $asueService
    ) {}

    /**
     * Execute the Asue cycle advancement logic.
     *
     * @param SimulateAsueCycleDTO $dto
     * @return array
     */
    public function execute(SimulateAsueCycleDTO $dto): array
    {
        return $this->asueService->advanceCycle($dto);
    }
}
