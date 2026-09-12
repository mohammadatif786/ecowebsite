<?php

namespace Database\Seeders;

use App\Models\UserSubscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'RCcMsfZkuLpMKB7WTepH',
                'amount' => 100,
                'user_id' => '5PZZFGCFskNJ3OkfXdpLuuxU09w1',
                'subscription_id' => '2106ef3973534df59150',
                'status' => true,
            ),
        );

        foreach ($data as $item) {
            $user = User::where('uid', $item['user_id'])->first();
            $item['user_id'] = $user?->id ?? null;
            UserSubscription::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
