<?php

namespace Database\Seeders;

use App\Models\ClubFete;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubFeteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '71b0357cdfaa49f09e8c',
                'end_date' => 1717113600000,
                'cost' => '12',
                'image_object' => NULL,
                'video_object' => NULL,
                'phone' => '1234567890',
                'www' => 'www.atlantis.com',
                'name' => 'VIP Ticket',
                'paid' => 'weekly',
                'location' => 'mohali',
                'is_paid' => false,
                'email' => 'admin@gmail.com',
                'status' => 1,
                'start_date' => 1716768000000,
            ),
        );
        foreach ($data as $item) {
            $item['end_date'] = Carbon::createFromTimestamp($item['end_date'] / 1000);
            $item['start_date'] = Carbon::createFromTimestamp($item['start_date'] / 1000);
            ClubFete::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
