<?php

namespace App\DTOs;

class ProductData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public float $price,
        public int $qty,
        public int $productCategoryId,
        public string $listingType,
        public ?int $sellerOwner,
        public ?string $imageUrl,
        public ?string $coverImage,
        public array $images = [],
        public string $commMode = 'pct',
        public float $commission = 10,
        public float $commFlat = 0,
        public bool $collectTax = false,
        public int $status = 1,
        public ?int $userId = null,
    ) {}
}
