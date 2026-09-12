<?php

namespace Database\Seeders;

use App\Models\Gift;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '0d26f4beff4a461e913f',
                'updated_at' => 1684893866000,
                'coins' => '15',
                'file_object' => 'gifts/_1684893866.png',
                'name' => 'Smiley Face',
                'created_at' => 1684893866000,
                'category' => 'collectibles',
            ),
            1 =>
            array(
                'firebase_id' => '16bd67421e2e4716936d',
                'updated_at' => 1673614891000,
                'coins' => '4',
                'file_object' => 'gifts/_1673614891.png',
                'name' => 'Diamond',
                'created_at' => 1673614891000,
                'category' => 'collectibles',
            ),
            2 =>
            array(
                'firebase_id' => '1e39f065477f4c528da7',
                'updated_at' => 1673428717000,
                'coins' => '32',
                'file_object' => 'gifts/_1673428715.txt',
                'name' => 'Test 2',
                'created_at' => 1673428717000,
                'category' => 'collectibles',
            ),
            3 =>
            array(
                'firebase_id' => '31668cabdf624912847a',
                'updated_at' => 1684894245000,
                'coins' => '10',
                'file_object' => 'gifts/_1684894245.png',
                'name' => 'Hot Pepper',
                'created_at' => 1684894245000,
                'category' => 'classic',
            ),
            4 =>
            array(
                'firebase_id' => '3c4c7db1ee1c40fead0d',
                'updated_at' => 1684894644000,
                'coins' => '33',
                'file_object' => 'gifts/_1684894644.png',
                'name' => 'Rose',
                'created_at' => 1684894644000,
                'category' => 'love',
            ),
            5 =>
            array(
                'firebase_id' => '5ac82024e54c4808a6f0',
                'updated_at' => 1684893994000,
                'coins' => '11',
                'file_object' => 'gifts/_1684893993.png',
                'name' => 'Teddy Bear',
                'created_at' => 1684893994000,
                'category' => 'love',
            ),
            6 =>
            array(
                'firebase_id' => '6b6734a1d35e4d77b597',
                'updated_at' => 1684894556000,
                'coins' => '15',
                'file_object' => 'gifts/_1684894556.png',
                'name' => 'Mojito',
                'created_at' => 1684894556000,
                'category' => 'moods',
            ),
            7 =>
            array(
                'firebase_id' => '950942dcd34c46979289',
                'updated_at' => 1684893451000,
                'coins' => '25',
                'file_object' => 'gifts/_1684893451.png',
                'name' => 'Diamond',
                'created_at' => 1684893451000,
                'category' => 'collectibles',
            ),
            8 =>
            array(
                'firebase_id' => 'a6140ccdcb8a49b7a08a',
                'updated_at' => 1684893406000,
                'coins' => '24',
                'file_object' => 'gifts/_1684893406.png',
                'name' => 'Banana Peel',
                'created_at' => 1684893406000,
                'category' => 'collectibles',
            ),
            9 =>
            array(
                'firebase_id' => 'ac39901f25144d5a9f58',
                'updated_at' => 1684893909000,
                'coins' => '19',
                'file_object' => 'gifts/_1684893909.png',
                'name' => 'Crown',
                'created_at' => 1684893909000,
                'category' => 'collectibles',
            ),
            10 =>
            array(
                'firebase_id' => 'b404a71b343d42aa8fc2',
                'updated_at' => 1684894298000,
                'coins' => '15',
                'file_object' => 'gifts/_1684894298.png',
                'name' => 'Chocolate',
                'created_at' => 1684894298000,
                'category' => 'love',
            ),
            11 =>
            array(
                'firebase_id' => 'c0ff4ac1d6a84975b634',
                'updated_at' => 1684893747000,
                'coins' => '31',
                'file_object' => 'gifts/_1684893746.png',
                'name' => 'Car',
                'created_at' => 1684893747000,
                'category' => 'collectibles',
            ),
            12 =>
            array(
                'firebase_id' => 'c8cace7c336c4e1ca2f1',
                'updated_at' => 1684894436000,
                'coins' => '18',
                'file_object' => 'gifts/_1684894435.png',
                'name' => 'Love',
                'created_at' => 1684894436000,
                'category' => 'love',
            ),
            13 =>
            array(
                'firebase_id' => 'e250e80e57e04153ac14',
                'updated_at' => 1684894397000,
                'coins' => '15',
                'file_object' => 'gifts/_1684894397.png',
                'name' => 'Christmas Tree',
                'created_at' => 1684894397000,
                'category' => 'collectibles',
            ),
            14 =>
            array(
                'firebase_id' => 'ed9959d71b8d4a7995d5',
                'updated_at' => 1684894497000,
                'coins' => '12',
                'file_object' => 'gifts/_1684894497.png',
                'name' => 'Hand Bag',
                'created_at' => 1684894497000,
                'category' => 'classic',
            ),
            15 =>
            array(
                'firebase_id' => 'f23445a077f1475fb0fa',
                'updated_at' => 1684894178000,
                'coins' => '29',
                'file_object' => 'gifts/_1684894178.png',
                'name' => 'Beer',
                'created_at' => 1684894178000,
                'category' => 'collectibles',
            ),
        );

        foreach ($data as $item) {
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at'] / 1000);
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at'] / 1000);
            Gift::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
