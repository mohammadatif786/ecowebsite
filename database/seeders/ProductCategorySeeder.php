<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '64582c143e91463c994a',
                'updated_at' =>
                array(
                    '_seconds' => 1680850631,
                    '_nanoseconds' => 700425000,
                ),
                'name' => 'Test 2',
                'created_at' =>
                array(
                    '_seconds' => 1680850631,
                    '_nanoseconds' => 700428000,
                ),
                'status' => false,
            ),
            1 =>
            array(
                'firebase_id' => '8f1e201cc81d402d83f7',
                'updated_at' =>
                array(
                    '_seconds' => 1680850544,
                    '_nanoseconds' => 140854000,
                ),
                'name' => 'General',
                'created_at' =>
                array(
                    '_seconds' => 1680850544,
                    '_nanoseconds' => 140856000,
                ),
                'status' => true,
            ),
            2 =>
            array(
                'firebase_id' => 'c36af2fc8f6349e28f07',
                'updated_at' =>
                array(
                    '_seconds' => 1680850554,
                    '_nanoseconds' => 933626000,
                ),
                'name' => 'Test',
                'created_at' =>
                array(
                    '_seconds' => 1680850554,
                    '_nanoseconds' => 933628000,
                ),
                'status' => true,
            ),
        );
        foreach ($data as $item) {
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at']['_seconds']);
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at']['_seconds']);
            ProductCategory::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
