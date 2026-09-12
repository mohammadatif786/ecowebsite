<?php

namespace Database\Seeders;

use App\Models\Payout;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'xhxdHKpJxVWD90ffxWjb',
                'amount' => 25,
                'currency' => 'USD',
                'method' => 'IBAN',
                'author' => 'Priya Sharma',
                'destination' => 'ZIRAAT U TR78 0001 0014 3086 9851 9950 01',
                'status' => 'processing',
                'reference' => 'N/A',
                'net_amount' => '0',
            ),
        );
        foreach ($data as $item) {
            Payout::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
