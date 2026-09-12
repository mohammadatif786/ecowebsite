<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '77cd23b870854b66871f',
                'duration_days' => 60,
                'stripe_price_id' => 'price_1RbJWqGMq2LaeuYkoHT5GnOx',
                'price' => '25.00',
                'description' => 'Silver Subscription',
                'name' => 'Silver',
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'emoji' => '🥈',
            ),
            1 =>
            array(
                'firebase_id' => 'c8da961b6c9c4a1b939a',
                'duration_days' => 30,
                'stripe_price_id' => 'price_1RbJWqGMq2LaeuYkheFpdw7C',
                'description' => 'Free for 1 month',
                'status' => 'active',
                'price' => '30.99',
                'name' => 'Gold',
                'billing_cycle' => 'monthly',
                'emoji' => '🥇',
            ),
            2 =>
            array(
                'firebase_id' => 'ed32dea669954be2bb96',
                'duration_days' => 30,
                'stripe_price_id' => 'price_1RbJWqGMq2LaeuYkFLfUyzq7',
                'price' => '45',
                'description' => 'Platinum  Subscription',
                'name' => 'Platinum',
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'emoji' => '💎',
            ),
        );
        foreach ($data as $item) {
            unset($item['createdAt']);
            unset($item['updatedAt']);
            SubscriptionPlan::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
