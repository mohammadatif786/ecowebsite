<?php

namespace App\DTOs;

class MerchantData
{
    public function __construct(
        public ?string $country,
        public ?string $state,
        public ?string $city,
        public ?string $name,
        public ?string $ownerName,
        public ?string $pickupLocations,
        public ?string $merchantType,
        public int     $userId,
        public bool    $offersPickup = true,
        public bool    $offersDelivery = false,
        public ?string $businessLicense = null,
        public ?string $vatCertificate = null,
        public ?string $businessLicenseFile = null,
        public ?string $vatCertificateFile = null,
    ) {}
}
