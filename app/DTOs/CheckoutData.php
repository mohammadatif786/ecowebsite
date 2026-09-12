<?php

namespace App\DTOs;

readonly class CheckoutData
{

    public function __construct(
        public int $userId,
        public array $items,
        public string $address,
        public string $city,
        public string $state,
        public string $country,
        public ?string $zip,
        public string $paymentMethod,
        public string $shippingMethod
    ) {}
}
