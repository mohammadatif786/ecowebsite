<?php

namespace Database\Seeders;

use App\Models\Frontend\WalletSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'addmoney_charge_percent',
                'value' => '2',
                'key' => 'addmoney_charge_percent',
            ),
            1 =>
            array(
                'firebase_id' => 'cashout_charge_percent',
                'value' => '4',
                'key' => 'cashout_charge_percent',
            ),
            2 =>
            array(
                'firebase_id' => 'currency',
                'value' => 'USD',
                'key' => 'currency',
            ),
            3 =>
            array(
                'firebase_id' => 'currency_symbol_position',
                'value' => 'left',
                'key' => 'currency_symbol_position',
            ),
            4 =>
            array(
                'firebase_id' => 'flutterwave',
                'value' => '{"status":"0","public_key":"aa","secret_key":"aa","hash":"aa"}',
                'key' => 'flutterwave',
            ),
            5 =>
            array(
                'firebase_id' => 'paypal',
                'value' => '{"status":"0","paypal_client_id":"nmmhhmhg","paypal_secret":"hghgggh"}',
                'key' => 'paypal',
            ),
            6 =>
            array(
                'firebase_id' => 'paystack',
                'value' => '{"status":"0","publicKey":null,"secretKey":null,"paymentUrl":null,"merchantEmail":null}',
                'key' => 'paystack',
            ),
            7 =>
            array(
                'firebase_id' => 'razor_pay',
                'value' => '{"status":"0","razor_key":null,"razor_secret":null}',
                'key' => 'razor_pay',
            ),
            8 =>
            array(
                'firebase_id' => 'sendmoney_charge_flat',
                'value' => '5',
                'key' => 'sendmoney_charge_flat',
            ),
            9 =>
            array(
                'firebase_id' => 'ssl_commerz_payment',
                'value' => '{"status":"1","store_id":"tujjjjjjjjjjj","store_password":"mgjjjjjjjjjjj"}',
                'key' => 'ssl_commerz_payment',
            ),
            10 =>
            array(
                'firebase_id' => 'stripe',
                'value' => '{"status":"0","published_key":null,"api_key":null}',
                'key' => 'stripe',
            ),
        );

        foreach ($data as $item) {
            WalletSettings::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
