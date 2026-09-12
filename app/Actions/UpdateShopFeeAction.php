<?php

namespace App\Actions;

use App\DTOs\ShopFeeData;
use App\Models\ShopFee;
use App\Repositories\ShopFeeRepository;

class UpdateShopFeeAction
{
    public function __construct(
        protected ShopFeeRepository $repository
    ) {}

    public function execute(ShopFeeData $data): ShopFee
    {
        return $this->repository->updateOrCreate($data);
    }
}
