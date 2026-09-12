<?php

namespace App\Actions;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\DTOs\ProductData;

class UpdateProductListingAction
{
    public function __construct(
        private ProductRepository $repo
    ) {}

    /**
     * Update price, qty, and status of a seller's listing.
     */
    public function execute(Product $product, float $price, int $qty, int $status): Product
    {
        $data = new ProductData(
            name:              $product->name,
            description:       $product->description,
            price:             $price,
            qty:               $qty,
            productCategoryId: $product->product_category_id,
            listingType:       $product->listing_type,
            sellerOwner:       $product->seller_owner,
            imageUrl:          $product->image_url,
            coverImage:        $product->cover_image,
            images:            $product->images ?? [],
            collectTax:        $product->collect_tax,
            status:            $status,
            userId:            $product->user_id,
        );

        return $this->repo->update($product, $data);
    }
}
