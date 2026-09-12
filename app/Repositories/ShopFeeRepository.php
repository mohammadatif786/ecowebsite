<?php

namespace App\Repositories;

use App\Models\ShopFee;
use App\DTOs\ShopFeeData;

class ShopFeeRepository
{
    public function getFee(): ShopFee
    {
        return ShopFee::first() ?? new ShopFee();
    }

    public function updateOrCreate(ShopFeeData $data): ShopFee
    {
        $fee = ShopFee::first() ?? new ShopFee();
        $fee->fill($data->toArray());
        $fee->save();
        return $fee;
    }
}
