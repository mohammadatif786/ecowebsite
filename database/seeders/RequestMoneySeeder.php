<?php

namespace Database\Seeders;

use App\Models\MoneyRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Request;

class RequestMoneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '1oA21353suJ3WNz6Tbyo',
                'amount' => 200,
                'to_user_id' => 'admin',
                'from_user_id' => '0U0NF1tshiQ3pGQHgu5MKyJHkUD2',
                'status' => 'approved',
            ),
            1 =>
            array(
                'firebase_id' => 'HUFxJnAUzIzT2Yz7nLd3',
                'amount' => 100,
                'from_user_id' => '0U0NF1tshiQ3pGQHgu5MKyJHkUD2',
                'to_user_id' => 'admin',
                'status' => 'approved',
            ),
        );

        foreach ($data as $item) {
            $user_from = User::where('uid', $item['from_user_id'])->first();
            $user_to = User::where('uid', $item['to_user_id'])->first();
            $item['from_user_id'] = $user_from?->id ?? null;
            $item['to_user_id'] = $user_to?->id ?? null;
            MoneyRequest::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
