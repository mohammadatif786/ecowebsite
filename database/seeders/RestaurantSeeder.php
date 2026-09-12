<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'd6825371871b490785bb',
                'end_date' => 1716940800,
                'country' => 'India',
                'cost' => '12',
                'image_object' => 'restaurants/dxfd_1716805481.png',
                'city' => 'Mohali',
                'video_object' => NULL,
                'phone' => '1234567890',
                'www' => 'www.atlantis.com',
                'name' => 'dxfd',
                'paid' => 'weekly',
                'location' => 'mohali',
                'state' => 'Punjab',
                'is_paid' => false,
                'email' => 'sachin.zeroit@gmail.com',
                'status' => 1,
                'start_date' => 1716854400,
            ),
        );
        foreach ($data as $item) {
            $item['end_date'] = Carbon::createFromTimestamp($item['end_date']);
            $item['start_date'] = Carbon::createFromTimestamp($item['start_date']);
            Restaurant::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
