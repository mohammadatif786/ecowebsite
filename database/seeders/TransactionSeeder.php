<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '24ff729e2b3d4d299a11',
                'note' => NULL,
                'balance' => '10',
                'user_id' => 'admin',
                'to_user_id' => 'hpsO0GaCTzUuDJ3MalbWTDYuMZA3',
                'created_at' => '2023-06-23',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-06-23',
            ),
            1 =>
            array(
                'firebase_id' => '3b95e322f63c428ab411',
                'note' => NULL,
                'balance' => '20',
                'user_id' => 'admin',
                'to_user_id' => 'nOQfj90bfNXqZ8FNdTDNcrSs9cX2',
                'created_at' => '2023-07-15',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-07-15',
            ),
            2 =>
            array(
                'firebase_id' => '423cd104b423446395e0',
                'note' => NULL,
                'balance' => '10',
                'user_id' => 'admin',
                'to_user_id' => 'Jj57ebWnWORpaYd5evyVg6eCo8B2',
                'created_at' => '2023-07-07',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-07-07',
            ),
            3 =>
            array(
                'firebase_id' => 'c8536fb5769543e4aa70',
                'note' => NULL,
                'balance' => 1000,
                'updated_at' => '',
                'user_id' => 'admin',
                'to_user_id' => 'admin',
                'ref_transaction_id' => NULL,
                'created_at' => '',
                'transaction_type' => 'cash_in',
                'debit' => 0,
                'credit' => '1000',
                'from_user_id' => 'admin',
            ),
            4 =>
            array(
                'firebase_id' => 'cc1e8902fcf343e898de',
                'note' => NULL,
                'balance' => '10',
                'user_id' => 'admin',
                'to_user_id' => 'skG7hoEfRSMxIZyO34fUhy4yt8R2',
                'created_at' => '2023-08-19',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-08-19',
            ),
            5 =>
            array(
                'firebase_id' => 'd4bd80c0d14047ef82bf',
                'note' => NULL,
                'balance' => '10',
                'user_id' => 'admin',
                'to_user_id' => 'hpsO0GaCTzUuDJ3MalbWTDYuMZA3',
                'created_at' => '2023-06-24',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-06-24',
            ),
            6 =>
            array(
                'firebase_id' => 'd66d0b6ac7764f55932f',
                'note' => NULL,
                'balance' => '10',
                'user_id' => 'admin',
                'to_user_id' => 'hpsO0GaCTzUuDJ3MalbWTDYuMZA3',
                'created_at' => '2023-06-23',
                'ref_transaction_id' => NULL,
                'transaction_type' => 'send_money',
                'from_user_id' => 'admin',
                'updatedAt' => '2023-06-23',
            ),
        );


        foreach ($data as $item) {
            $fromUser = User::where('uid', $item['from_user_id'])->first();
            $toUser = User::where('uid', $item['to_user_id'])->first();

            $uuid = $item['firebase_id'];

            $cleanData = [
                'uuid' => $uuid,
                'from_type' => $fromUser ? User::class : null,
                'from_id' => $fromUser?->id,
                'to_type' => $toUser ? User::class : null,
                'to_id' => $toUser?->id,
                'amount' => (float) ($item['balance'] ?? 0),
                'currency' => 'USD',
                'status' => 'success',
                'processor_id' => 'system',
                'batch' => 1,
                'meta' => [
                    'note' => $item['note'] ?? null,
                    'transaction_type' => $item['transaction_type'] ?? null,
                ],
                'created_at' => now(),
            ];

            Transaction::updateOrCreate(
                ['uuid' => $uuid],
                $cleanData
            );
        }
    }
}
