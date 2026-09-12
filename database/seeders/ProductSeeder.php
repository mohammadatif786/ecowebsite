<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '7a33592b76ab4220a585',
                'images' =>
                array(
                    0 => 'products/_16808589550.png',
                    1 => 'products/_16808589561.png',
                    2 => 'products/_16808589562.png',
                ),
                'product_category_id' => '8f1e201cc81d402d83f7',
                'price' => '100',
                'qty' => '10',
                'description' => 'test product',
                'created_at' =>
                array(
                    '_seconds' => 1680858957,
                    '_nanoseconds' => 387723000,
                ),
                'cover_image' => 'products/_1680858954.jpg',
                'status' => true,
                'updated_at' =>
                array(
                    '_seconds' => 1680859126,
                    '_nanoseconds' => 96725000,
                ),
                'name' => 'Product 112',
            ),
        );

        foreach ($data as $item) {
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at']['_seconds']);
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at']['_seconds']);
            $cat = ProductCategory::where('firebase_id', $item['product_category_id'])->first();
            $item['product_category_id'] = $cat?->id ?? null;
            Product::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
