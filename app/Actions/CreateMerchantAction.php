<?php

namespace App\Actions;

use App\Models\Merchants;
use App\Repositories\MerchantRepository;
use App\DTOs\MerchantData;

class CreateMerchantAction
{
    public function __construct(
        private MerchantRepository $merchants
    ) {}

    public function execute(MerchantData $data): Merchants
    {
        return $this->merchants->create($data);
    }
}
