<?php

namespace App\Actions;

use App\DTOs\AsueData;
use App\Models\Asue;
use App\Repositories\AsueRepository;

class AsueAction
{
    public function __construct(
        protected AsueRepository $repository
    ) {}

    /**
     * Execute the Asue creation logic.
     */
    public function execute(array $requestData): Asue
    {
        $dto = AsueData::fromRequest($requestData);

        return $this->repository->createAsue($dto);
    }
}