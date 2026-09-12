<?php

namespace Database\Seeders;

use App\Models\Frontend\LiveStreamLinkUp;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiveStreamsLinkupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'k6yfcXmo13KGi66U2xx7',
                'channel_name' => 'Miya',
                'type' => 'public',
                'user_id' => 's3mxMNxSzyfIUpmkpepZq3yFXB12',
                'status' => '1',
                'join' => 4,
            ),
        );
        foreach ($data as $item) {
            $user = User::where('uid', $item['user_id'])->first();
            $item['user_id'] = $user?->id ?? null;
            LiveStreamLinkUp::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
