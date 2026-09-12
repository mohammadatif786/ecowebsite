<?php

namespace Database\Seeders;

use App\Models\Frontend\BankInfo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 => array(
                'firebase_id' => 'EugFr7rHF2VOms8KLEBK',
                'routing_number' => '2488421',
                'uid' => 'ELWwilGfMEWC4fdw6Z2fum2TH1X2',
                'cash_app_id' => '122654555',
                'bank_name' => 'bank of Bahamas ',
                'paypal_id' => 'cstuart@caribmedltd.com',
                'bank_account' => '124358977',
                'zell_id' => '12665774',
            ),
            1 => array(
                'firebase_id' => 'KPZaFSAnE9TAjp15IhGF',
                'routing_number' => '2583494',
                'uid' => 'sLW3YdFUTwfmDwNgHIipnyuhHMJ2',
                'cash_app_id' => '2580',
                'bank_name' => 'hhhh',
                'paypal_id' => 'jjjjjjjjj@gmail.com',
                'bank_account' => '518463746',
                'zell_id' => '2518649',
            ),
            2 => array(
                'firebase_id' => 'cff084b813054176806c',
                'routing_number' => '',
                'uid' => 'PS9xyC1w9GcCsPoJRYNIOOKGoL22',
                'cash_app_id' => '',
                'bank_name' => '',
                'paypal_id' => '',
                'bank_account' => '',
                'zell_id' => '',
            ),
            3 => array(
                'firebase_id' => 'ypSG4cZY44PrdAGwtDqI',
                'uid' => 'sqah07OvGdPFPJpT2xZmTcvkObg2',
                'routing_number' => '6323',
                'cash_app_id' => '56532',
                'paypal_id' => 'dawoodqurashi448@gmail.com',
                'bank_name' => 'National Institute ',
                'zell_id' => '498468495645645',
                'bank_account' => '12345657890',
            ),
        );

        foreach ($data as $item) {
            $user = User::where('uid', $item['uid'])->first();
            $item['user_id'] = $user?->id ?? null;
            BankInfo::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
