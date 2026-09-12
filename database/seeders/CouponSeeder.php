<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\LinkUpEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '0b218852db374b8cb2b7',
                'firebase_event_id' => 'b4f9b7368737438d87bd',
                'code' => 'Believe',
                'image_object' => 'coupons/Cassius_&_Julien_Birthday_Bash_1683722351.jpg',
                'updated_at' => 1683722352000,
                'expiry_date' => '2023-05-12',
                'description' => 'The Best Wine in the Caribbean.',
                'discount' => '24.04',
                'created_at' => 1683722352000,
                'title' => 'Cassius & Julien Birthday Bash',
                'discount_type' => 'amount',
                'status' => 'live',
            ),
            1 =>
            array(
                'firebase_id' => '41fb86a413e74350b7e1',
                'firebase_event_id' => '0ae0d6ae5d204d99a851',
                'code' => '5555',
                'image_object' => 'coupons/Coupon_1686749528.jpg',
                'updated_at' => 1686749528000,
                'expiry_date' => '2023-06-15',
                'description' => 'Coupon',
                'discount' => '80',
                'created_at' => 1686749528000,
                'title' => 'Coupon',
                'discount_type' => 'amount',
                'status' => 'live',
            ),
            2 =>
            array(
                'firebase_id' => '51ecf301978f4eeba8ad',
                'firebase_event_id' => '0268827e6bbe46789578',
                'code' => '1w3e4r',
                'image_object' => 'coupons/Testing_coupon_1689230088.png',
                'updated_at' => 1689230088000,
                'expiry_date' => '2023-07-31',
                'description' => 'Testing coupon',
                'discount' => '10',
                'created_at' => 1689230088000,
                'title' => 'Testing coupon',
                'discount_type' => 'amount',
                'status' => 'live',
            ),
            3 =>
            array(
                'firebase_id' => '8e1e09f6bc9a41f8bd73',
                'firebase_event_id' => '0268827e6bbe46789578',
                'code' => '12345',
                'image_object' => 'coupons/Test_evet_1689407687.jpg',
                'updated_at' => 1689407688000,
                'expiry_date' => '2023-07-29',
                'description' => 'test',
                'discount' => '100',
                'created_at' => 1689407688000,
                'title' => 'Test evet',
                'discount_type' => 'percentage',
                'status' => 'live',
            ),
            4 =>
            array(
                'firebase_id' => '9zmpGVQ12Mrb6RWrezIY',
            ),
            5 =>
            array(
                'firebase_id' => 'f42fd0a4bb1042d7851a',
                'firebase_event_id' => 'b4f9b7368737438d87bd',
                'code' => 'Shenique',
                'image_object' => 'coupons/Cassius_&_Julien_Birthday_Bash_1683679889.heic',
                'updated_at' => 1683679889000,
                'expiry_date' => '2023-05-11',
                'description' => 'A gift from your beyond headlines team. We Love you',
                'discount' => '24.04',
                'created_at' => 1683679889000,
                'title' => 'Cassius & Julien Birthday Bash',
                'discount_type' => 'amount',
                'status' => 'live',
            ),
            6 =>
            array(
                'firebase_id' => 'fcc19c8ea1a3447e9999',
                'firebase_event_id' => 'f879a1aee5fd4d3593a5',
                'code' => '222222',
                'image_object' => NULL,
                'updated_at' => 1714989916000,
                'expiry_date' => '2024-06-30',
                'description' => 'testing',
                'discount' => '20',
                'created_at' => 1714989916000,
                'title' => 'test',
                'discount_type' => 'percentage',
                'status' => 'live',
            ),
        );

        DB::table('coupons')->truncate();
        foreach ($data as $item) {
            unset($item['created_at']);
            unset($item['updated_at']);
            if (array_key_exists('firebase_event_id', $item)) {
                $event = LinkUpEvent::where('firebase_id', $item['firebase_event_id'])->first();
                $item['link_up_event_id'] = $event?->id ?? null;
                Coupon::create($item);
            }
        }
    }
}
