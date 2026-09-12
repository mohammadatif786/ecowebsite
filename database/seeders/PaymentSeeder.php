<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => 'wauF8fOonRGD42sUW3FY',
                'transaction_id' => 'dsfdsfdf3e2342342342343243243',
                'method' => 'Google Play',
                'price' => '222',
                'payer_id' => 'dsfdsfk3j24343',
                'currency' => 'USD',
                'type' => 'consumable',
                'sku' => '34gf',
                'payer' => 'Test',
            ),
        );
        foreach ($data as $item) {
            // Check if transaction_id is a column in the table
            if (!\Illuminate\Support\Facades\Schema::hasColumn('payments', 'transaction_id')) {
                unset($item['transaction_id']);
            } else {
                $tr = Transaction::where('uuid', $item['transaction_id'])->first();
                $item['transaction_id'] = $tr?->id ?? null;
            }

            Payment::updateOrCreate(
                 ['firebase_id' => $item['firebase_id']],
                 $item
             );
        }
    }
}
