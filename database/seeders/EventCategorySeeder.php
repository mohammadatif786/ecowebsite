<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = array(
            0 =>
            array(
                'uid' => '0ba250b13e0f43daac39',
                'name' => 'Music',
                'created_at' =>
                array(
                    '_seconds' => 1679503711,
                    '_nanoseconds' => 52859000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679699245.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679699246,
                    '_nanoseconds' => 406984000,
                ),
            ),
            1 =>
            array(
                'uid' => '2cd8a27570e642cb9e58',
                'name' => 'Cookouts/Food',
                'created_at' =>
                array(
                    '_seconds' => 1679503786,
                    '_nanoseconds' => 795199000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676615.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676616,
                    '_nanoseconds' => 85203000,
                ),
            ),
            2 =>
            array(
                'uid' => '449357f6ae6c44ebbd4b',
                'name' => 'Arts',
                'created_at' =>
                array(
                    '_seconds' => 1679503599,
                    '_nanoseconds' => 926643000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676656.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676656,
                    '_nanoseconds' => 965428000,
                ),
            ),
            3 =>
            array(
                'uid' => '6e24b8a2658e4c55b5ca',
                'name' => 'Online Events',
                'created_at' =>
                array(
                    '_seconds' => 1679503915,
                    '_nanoseconds' => 572247000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676771.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676771,
                    '_nanoseconds' => 846381000,
                ),
            ),
            4 =>
            array(
                'uid' => '750350d236b643958fd3',
                'name' => 'Free',
                'created_at' =>
                array(
                    '_seconds' => 1679503928,
                    '_nanoseconds' => 224763000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676793.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676793,
                    '_nanoseconds' => 856407000,
                ),
            ),
            5 =>
            array(
                'uid' => '897ce771bac14ab99f4f',
                'created_at' =>
                array(
                    '_seconds' => 1679300238,
                    '_nanoseconds' => 24874000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676842.png',
                'updated_at' =>
                array(
                    '_seconds' => 1680796156,
                    '_nanoseconds' => 636802000,
                ),
                'name' => 'Sport/Fitness',
            ),
            6 =>
            array(
                'uid' => 'ab71a461e4414ab3bb8e',
                'name' => 'Church',
                'created_at' =>
                array(
                    '_seconds' => 1679503764,
                    '_nanoseconds' => 756511000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676877.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676877,
                    '_nanoseconds' => 639394000,
                ),
            ),
            7 =>
            array(
                'uid' => 'b3ab2fe43ef94125acaa',
                'created_at' =>
                array(
                    '_seconds' => 1679503871,
                    '_nanoseconds' => 878332000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676908.png',
                'updated_at' =>
                array(
                    '_seconds' => 1680796104,
                    '_nanoseconds' => 410208000,
                ),
                'name' => 'Charity',
            ),
            8 =>
            array(
                'uid' => 'c7f63f38eb9a42d49069',
                'name' => 'Food',
                'created_at' =>
                array(
                    '_seconds' => 1679503751,
                    '_nanoseconds' => 473611000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676954.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676954,
                    '_nanoseconds' => 974841000,
                ),
            ),
            9 =>
            array(
                'uid' => 'd950241e93774a499492',
                'name' => 'Party/Fete',
                'created_at' =>
                array(
                    '_seconds' => 1679503737,
                    '_nanoseconds' => 116337000,
                ),
                'is_featured' => true,
                'status' => true,
                'image_object' => 'eventCategories/_1679676994.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679676995,
                    '_nanoseconds' => 263015000,
                ),
            ),
            10 =>
            array(
                'uid' => 'dc02b6225e7a4d75bcff',
                'name' => 'Business',
                'created_at' =>
                array(
                    '_seconds' => 1679626688,
                    '_nanoseconds' => 560562000,
                ),
                'status' => true,
                'is_featured' => true,
                'image_object' => 'eventCategories/_1679699284.png',
                'updated_at' =>
                array(
                    '_seconds' => 1679699285,
                    '_nanoseconds' => 68757000,
                ),
            ),
        );

        foreach ($categories as $category) {
            $category['created_at'] = Carbon::now();
            $category['updated_at'] = Carbon::now();
            // Log::info($category);
            EventCategory::updateOrCreate(
                ['uid' => $category['uid']],
                $category
            );
        }
    }
}
