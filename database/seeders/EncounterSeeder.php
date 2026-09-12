<?php

namespace Database\Seeders;

use App\Models\Encounter;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EncounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'nM2hFpRd0TN5fAgBOvDg',
                'date' => 1673258807,
                'from' => 'John Doe',
                'to' => 'jane Doe',
                'liked' => false,
                'seen' => true,
            ),
        );
        foreach ($data as $item) {
            $item['date'] = Carbon::createFromTimestamp($item['date']);
            Encounter::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
