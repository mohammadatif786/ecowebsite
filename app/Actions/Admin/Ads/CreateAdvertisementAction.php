<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\AdvertisementData;
use App\Repositories\AdvertisementRepository;
use App\Models\Advertisement;

class CreateAdvertisementAction
{
    public function __construct(
        private AdvertisementRepository $repository
    ) {}

    public function execute(AdvertisementData $data): Advertisement
    {
        return $this->repository->create($data);
    }
}
