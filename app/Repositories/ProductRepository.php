<?php

namespace App\Repositories;

use App\DTOs\ProductData;
use App\Models\Product;

class ProductRepository
{
    public function create(ProductData $data): Product
    {
        return Product::create([
            'user_id' => $data->userId,
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'qty' => $data->qty,
            'product_category_id' => $data->productCategoryId,
            'listing_type' => $data->listingType,
            'seller_owner' => $data->sellerOwner,
            'image_url' => $data->imageUrl,
            'cover_image' => $data->coverImage,
            'status' => $data->status,
            'images' => $data->images,
            'commMode' => $data->commMode,
            'commission' => $data->commission,
            'commFlat' => $data->commFlat,
            'collect_tax' => $data->collectTax,
        ]);
    }

    public function update(Product $product, ProductData $data): Product
    {
        $product->update([
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'qty' => $data->qty,
            'product_category_id' => $data->productCategoryId,
            'listing_type' => $data->listingType,
            'seller_owner' => $data->sellerOwner,
            'image_url' => $data->imageUrl,
            'cover_image' => $data->coverImage ?? $product->cover_image,
            'images' => $data->images ?: $product->images,
            'status' => $data->status,
            'commMode' => $data->commMode,
            'commission' => $data->commission,
            'commFlat' => $data->commFlat,
            'collect_tax' => $data->collectTax,
        ]);

        return $product;
    }
}
