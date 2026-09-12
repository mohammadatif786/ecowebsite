<?php

namespace Database\Seeders;

use App\Models\CashOut;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashOutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'firebase_id' => 'sqah07OvGdPFPJpT2xZmTcvkObg2',
                'cash_out_id' => '95c05d20-d779-11ed-a0db-33d738045119',
                'cash_out_amount' => '23',
            ],
            [
                'firebase_id' => 'GVMNvD1fUeexifzkTYws1Afdaxh2',
                'cash_out_id' => '4c0af430-c336-11ed-9495-e9d29e5076fc',
                'cash_out_amount' => '2356',
            ],
            [
                'firebase_id' => 'VBnEvcwF0KPj8KuZD0lJpeVfpq53',
                'cash_out_id' => '24671120-c732-11ed-9cf1-03bba36612f2',
                'cash_out_amount' => '2314',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => 'cc0b41b0-ee44-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '20',
            ],
            [
                'firebase_id' => '8aB58BvpfMXk9UiwQRcKIL6kPOj1',
                'cash_out_id' => 'bb6deb90-0079-11ee-9523-5dd324b9494d',
                'cash_out_amount' => '55',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '5ce60a40-ee44-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '20',
            ],
            [
                'firebase_id' => 'WwtPDKs5bpf70qTGbnGYiMxAYTG3',
                'cash_out_id' => 'ddf71d90-d4cc-11ed-a70e-4943c14764f0',
                'cash_out_amount' => '1111',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '385b3910-ee45-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '20',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '68886730-ee44-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '20',
            ],
            [
                'firebase_id' => 'sLW3YdFUTwfmDwNgHIipnyuhHMJ2',
                'cash_out_id' => '1b8a1410-bc21-11ed-a881-1fc6b3a086c6',
                'cash_out_amount' => '2580',
            ],
            [
                'firebase_id' => '8aB58BvpfMXk9UiwQRcKIL6kPOj1',
                'cash_out_id' => 'c17ef5b0-0079-11ee-9523-5dd324b9494d',
                'cash_out_amount' => '555',
            ],
            [
                'firebase_id' => 'N0jZszshI4Mo8InJD2UMAxYH8UO2',
                'cash_out_id' => 'c8099280-d8ee-11ed-9048-8bc5360102ef',
                'cash_out_amount' => '100',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '28e69b40-ee41-11ed-8173-3ffebde46630',
                'cash_out_amount' => '25',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '5fd56510-efd0-11ed-9859-454c3c238b22',
                'cash_out_amount' => '25',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '5be34ad0-ee45-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '20',
            ],
            [
                'firebase_id' => '0U0NF1tshiQ3pGQHgu5MKyJHkUD2',
                'cash_out_amount' => '500',
            ],
            [
                'firebase_id' => 'sqah07OvGdPFPJpT2xZmTcvkObg2',
                'cash_out_id' => '69040740-d784-11ed-8db6-2df21c538cba',
                'cash_out_amount' => '36',
            ],
            [
                'firebase_id' => 'sLW3YdFUTwfmDwNgHIipnyuhHMJ2',
                'cash_out_id' => '25a86910-bc21-11ed-a881-1fc6b3a086c6',
                'cash_out_amount' => '100',
            ],
            [
                'firebase_id' => 'rZA2X1ZKiFP4ZyR93DN6P0y6nca2',
                'cash_out_id' => '520c0760-d5cf-11ed-8947-b12d6342bd4e',
                'cash_out_amount' => '30',
            ],
            [
                'firebase_id' => 'sqah07OvGdPFPJpT2xZmTcvkObg2',
                'cash_out_id' => '74165930-d784-11ed-8db6-2df21c538cba',
                'cash_out_amount' => '9',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '21ea46c0-ee41-11ed-8173-3ffebde46630',
                'cash_out_amount' => '12',
            ],
            [
                'firebase_id' => 'rZA2X1ZKiFP4ZyR93DN6P0y6nca2',
                'cash_out_id' => '4543ef20-d5cf-11ed-8947-b12d6342bd4e',
                'cash_out_amount' => '1000',
            ],
            [
                'firebase_id' => 'VXhHtnF0kiUjz7yau0RjQ139Byh1',
                'cash_out_id' => '6c7e15f0-ee45-11ed-bd62-a5cd6db33e5b',
                'cash_out_amount' => '10',
            ],
        ];

        DB::table('cash_outs')->truncate();
        foreach ($data as $item) {
            CashOut::create($item);
        }
    }
}
