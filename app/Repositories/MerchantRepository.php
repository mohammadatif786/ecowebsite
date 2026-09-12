<?php

namespace App\Repositories;

use App\DTOs\MerchantData;
use App\Models\Merchants;

class MerchantRepository
{
    public function create(MerchantData $data): Merchants
    {
        return Merchants::create([
            'user_id' => $data->userId,
            'country' => $data->country,
            'state' => $data->state,
            'city' => $data->city,
            'name' => $data->name,
            'owner_name' => $data->ownerName,
            'pickup_locations' => json_encode($data->pickupLocations),
            'merchant_type' => $data->merchantType,
            'offers_pickup' => $data->offersPickup,
            'offers_delivery' => $data->offersDelivery,
            'business_license' => $data->businessLicense,
            'vat_certificate' => $data->vatCertificate,
            'business_license_file' => $data->businessLicenseFile,
            'vat_certificate_file' => $data->vatCertificateFile,
        ]);
    }
}
