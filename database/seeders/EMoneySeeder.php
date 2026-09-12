<?php

namespace Database\Seeders;

use App\Models\Frontend\EMoney;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EMoneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '744500ecdbc547559e2b',
                'user_id' => 'nOQfj90bfNXqZ8FNdTDNcrSs9cX2',
                'current_balance' => '20',
                'charge_earned' => 0,
            ),
            1 =>
            array(
                'firebase_id' => 'a3792b0161694a7aa28c',
                'user_id' => 'skG7hoEfRSMxIZyO34fUhy4yt8R2',
                'current_balance' => '10',
                'charge_earned' => 0,
            ),
            2 =>
            array(
                'firebase_id' => 'c13df7efffa14b329d6f',
                'user_id' => 'admin',
                'charge_earned' => 0,
                'current_balance' => 930,
            ),
            3 =>
            array(
                'firebase_id' => 'd61835a157cc4973a231',
                'user_id' => 'hpsO0GaCTzUuDJ3MalbWTDYuMZA3',
                'charge_earned' => 0,
                'current_balance' => 30,
            ),
            4 =>
            array(
                'firebase_id' => 'f0709381a4f841d9a7f8',
                'user_id' => 'Jj57ebWnWORpaYd5evyVg6eCo8B2',
                'current_balance' => '10',
                'charge_earned' => 0,
            ),
        );

        foreach ($data as $item) {
            $user = User::where('uid', $item['user_id'])->first();
            $item['user_id'] = $user?->id ?? null;
            EMoney::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
