<?php

namespace App\DTOs;

class OrderTracking
{
    public function __construct(
        public string $orderId,
        public string $status,
        public ?string $note = null,
        public array $meta = [],
        public ?\DateTimeInterface $lastUpdate = null,
    ) {
    }
}
