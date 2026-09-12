<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '5FvWLZwzXBUc1f1EfWH9',
                'user_id' => '5PZZFGCFskNJ3OkfXdpLuuxU09w1',
                'number' => 'OR34324',
                'total' => 100,
                'created_at' =>
                array(
                    '_seconds' => 1680868676,
                    '_nanoseconds' => 424000000,
                ),
                'country' => 'Pakistan',
                'city' => 'Islamabad',
                'state' => 'Islamabad',
                'zipcode' => 'dsf3234',
                'street_address' => 'test address',

                'updated_at' =>
                array(
                    '_seconds' => 1681104140,
                    '_nanoseconds' => 355730000,
                ),
                'status' => 'new',
            ),
        );

        $items = array(
            0 =>
            array(
                'product_id' => '7a33592b76ab4220a585',
                'qty' => '3',
                'unit_price'=>100,
                'sub_total'=>300
            ),
        );

        foreach ($data as $item) {
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at']['_seconds']);
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at']['_seconds']);
            $user = User::where('uid', $item['user_id'])->first();
            $item['user_id']=$user?->id??null;
            $order = Order::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
            foreach ($items as $i) {
                $product = Product::where('firebase_id', '7a33592b76ab4220a585')->first();
                OrderItem::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'qty' => $i['qty'],
                        'unit_price' => $i['unit_price'],
                        'sub_total' => $i['sub_total'],
                    ]
                );
            }
        }
    }
}
