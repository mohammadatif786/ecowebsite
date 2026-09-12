<?php

namespace App\DTOs;

class CheckoutPricing
{
    public function __construct(
        public float $subtotal,
        public float $tax,
        public float $shipping,
        public float $discount,
        public float $net,
        public array $lines
    ) {}
}
