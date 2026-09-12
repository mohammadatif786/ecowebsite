<?php

namespace Database\Seeders;

use App\Models\LinkUpEvent;
use App\Models\Sponsor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '4712b11d524f4d1b81c3',
                'image_object' => 'sponsore/_1681832277.jpg',
                'name' => 'Heniken Beer',
                'description' => 'The beer f the Bahamas',
                'created_at' => 1681832277000,
                'status' => true,
                'event_id' => '1d5b911d8b0f42a89ee8',
                'updated_at' => 1689910846000,
            ),
            1 =>
            array(
                'firebase_id' => '4fcb90add3464baeb1b4',
                'event_id' => '81b1f5c38d0c4539a27d',
                'image_object' => 'sponsore/_1681229875.jpg',
                'name' => 'Raggie Night',
                'created_at' => 1681229875000,
                'status' => true,
                'updated_at' => 1681230786000,
                'description' => 'This is a test for thew rest',
            ),
            2 =>
            array(
                'firebase_id' => '5d9fb2492ced41b98997',
                'event_id' => 'af390477953d4f13a2ec',
                'image_object' => 'sponsore/_1681532396.jpg',
                'name' => 'Heiniken Beeer',
                'created_at' => 1681532396000,
                'status' => true,
                'updated_at' => 1681532477000,
                'description' => 'The is the onlly beer you what nned in the bahamas. So get the beer that is good for you and your fiamilu',
            ),
            3 =>
            array(
                'firebase_id' => '63b6c7047f6848ddae68',
                'event_id' => 'fd87fd11afd546148ba8',
                'image_object' => 'sponsore/_1681427792.jpg',
                'updated_at' => 1681427792000,
                'name' => 'wild weeeknd',
                'description' => 'This is weekend to rememebt',
                'created_at' => 1681427792000,
                'status' => true,
            ),
            4 =>
            array(
                'firebase_id' => '687c467f1608486d8bb9',
                'event_id' => 'b812aebe49944bb492f2',
                'image_object' => 'sponsore/_1681915943.jpg',
                'updated_at' => 1681915943000,
                'name' => 'Believe Wines',
                'description' => 'The wine of the Bahamas',
                'created_at' => 1681915943000,
                'status' => true,
            ),
            5 =>
            array(
                'firebase_id' => '8UmraKPNPPC7trj1BCjC',
                'sponsor_image_object' => 'https://firebasestorage.googleapis.com/v0/b/linkup-cfa21.appspot.com/o/SponsorImages%2F1682687981152?alt=media&token=d8702abd-ee24-4b5c-b15a-28d0df0ecc01',
                'name' => 'gg',
                'description' => 'hhh',
                'created_at' => 1682688020,
                'event' => 'Coconut Grove Arts Festival 2023',
                'status' => true,
            ),
            6 =>
            array(
                'firebase_id' => '9b8aa1fc24554658b43d',
                'event_id' => '06f891391f504059a87e',
                'image_object' => 'sponsore/_1681783693.jpg',
                'updated_at' => 1681783693000,
                'name' => 'Heniken Beer',
                'description' => 'The beer iid redy',
                'created_at' => 1681783693000,
                'status' => true,
            ),
            7 =>
            array(
                'firebase_id' => 'Cl2ZddHkL3lEpjKPM1Ip',
                'sponsor_image_object' => 'https://firebasestorage.googleapis.com/v0/b/linkup-cfa21.appspot.com/o/SponsorImages%2F1682517918491?alt=media&token=7144b9f7-fcd7-470b-a150-96310d0ca15a',
                'name' => 'ok',
                'description' => 'vshs',
                'created_at' =>
                array(
                    '_seconds' => 1682517923,
                    '_nanoseconds' => 240000000,
                ),
                'event' => 'Tingum Dem Wednesdays',
                'status' => true,
            ),
            8 =>
            array(
                'firebase_id' => 'e5decc8bbf544c6a80fb',
                'event_id' => 'd8310927c0984c8a804c',
                'image_object' => 'sponsore/_1689668261.png',
                'updated_at' => 1689668262000,
                'name' => 'VIP Ticketfcbvdfbdfs',
                'description' => 'sacsaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'created_at' => 1689668262000,
                'status' => true,
            ),
        );

        foreach ($data as $item) {
            unset($item['created_at']);
            unset($item['updated_at']);
            if (array_key_exists('event_id', $item)) {
                $event = LinkUpEvent::where('firebase_id', $item['event_id'])->first();
                $item['link_up_event_id'] = $event?->id ?? null;
                unset($item['event_id']);
            }
            Sponsor::updateOrCreate(
                 ['firebase_id' => $item['firebase_id']],
                 $item
             );
        }
    }
}
