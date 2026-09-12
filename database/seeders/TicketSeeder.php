<?php

namespace Database\Seeders;

use App\Models\LinkUpEvent;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '002dbdee236d42f495be',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1717156800,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1714989434,
                'description' => 'testdemo',
                'created_at' =>
                array(
                    '_seconds' => 1714989434,
                    '_nanoseconds' => 993574000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => 'f879a1aee5fd4d3593a5',
                'updated_at' =>
                array(
                    '_seconds' => 1714989434,
                    '_nanoseconds' => 993572000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1714996800,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'testdemo',
                'status' => true,
                'qty_available' => '9',
                'qty_sold' => 1,
            ),
            1 =>
            array(
                'firebase_id' => '1d5c9a77adfe4d919816',
                'max_qty_per_order' => '2',
                'sales_end' =>
                array(
                    '_seconds' => 1697457600,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1697027061,
                'description' => 'free for all',
                'created_at' =>
                array(
                    '_seconds' => 1697027061,
                    '_nanoseconds' => 656626000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => 'fd7917e729d44946bf75',
                'updated_at' =>
                array(
                    '_seconds' => 1697027061,
                    '_nanoseconds' => 656624000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1697025600,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'General Ticket',
                'status' => true,
                'qty_available' => -13,
                'qty_sold' => 23,
            ),
            2 =>
            array(
                'firebase_id' => '23094972648048baa2d4',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1703246400,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1702948177,
                'description' => 'General Admission include food and 1 drink',
                'created_at' =>
                array(
                    '_seconds' => 1702948177,
                    '_nanoseconds' => 305128000,
                ),
                'type' => 'paid',
                'qty_available' => '150',
                'min_qty_per_order' => '1',
                'event_id' => '30fb581e4eea407b90ba',
                'updated_at' =>
                array(
                    '_seconds' => 1702948177,
                    '_nanoseconds' => 305127000,
                ),
                'price' => '25',
                'sales_start' =>
                array(
                    '_seconds' => 1702900800,
                    '_nanoseconds' => 0,
                ),
                'qty' => '150',
                'name' => 'General Ticket',
                'status' => true,
                'qty_sold' => 0,
            ),
            3 =>
            array(
                'firebase_id' => '29b9c5b926734cf7bdf1',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1703246400,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1702952385,
                'description' => 'testy',
                'created_at' =>
                array(
                    '_seconds' => 1702952385,
                    '_nanoseconds' => 401170000,
                ),
                'type' => 'paid',
                'qty_available' => '20',
                'min_qty_per_order' => '1',
                'event_id' => 'e161a04e996d4c4cba04',
                'updated_at' =>
                array(
                    '_seconds' => 1702952385,
                    '_nanoseconds' => 401168000,
                ),
                'price' => '23',
                'sales_start' =>
                array(
                    '_seconds' => 1702900800,
                    '_nanoseconds' => 0,
                ),
                'qty' => '20',
                'name' => 'General Ticket',
                'status' => true,
                'qty_sold' => 0,
            ),
            4 =>
            array(
                'firebase_id' => '2a81c46113ce4cc2b19c',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1719748800,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1717496198,
                'description' => 'free',
                'created_at' =>
                array(
                    '_seconds' => 1717496198,
                    '_nanoseconds' => 823695000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => '4c89ba9652cb4c87999c',
                'updated_at' =>
                array(
                    '_seconds' => 1717496198,
                    '_nanoseconds' => 823693000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1717502400,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'free',
                'status' => true,
                'qty_sold' => 2,
                'qty_available' => '8',
            ),
            5 =>
            array(
                'firebase_id' => '3cb685334eb741abbdd7',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1703332800,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1703044584,
                'description' => 'frwee',
                'created_at' =>
                array(
                    '_seconds' => 1703044584,
                    '_nanoseconds' => 340384000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => '429c215c1d9a4f41a751',
                'updated_at' =>
                array(
                    '_seconds' => 1703044584,
                    '_nanoseconds' => 340382000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1702987200,
                    '_nanoseconds' => 0,
                ),
                'qty' => '20',
                'name' => 'Free For all',
                'status' => true,
                'qty_available' => 9,
                'qty_sold' => 11,
            ),
            6 =>
            array(
                'firebase_id' => '4618ff88eb3447328747',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1703246400,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1702950169,
                'description' => 'free',
                'created_at' =>
                array(
                    '_seconds' => 1702950169,
                    '_nanoseconds' => 407174000,
                ),
                'type' => 'free',
                'qty_available' => '12',
                'min_qty_per_order' => '1',
                'event_id' => '40c0b22f22a64b99bcc1',
                'updated_at' =>
                array(
                    '_seconds' => 1702950169,
                    '_nanoseconds' => 407172000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1702987200,
                    '_nanoseconds' => 0,
                ),
                'qty' => '12',
                'name' => 'Free For all',
                'status' => true,
                'qty_sold' => 0,
            ),
            7 =>
            array(
                'firebase_id' => '48b360aaeeb04ede9ca2',
                'max_qty_per_order' => '10',
                'description' => 'freeeee',
                'created_at' =>
                array(
                    '_seconds' => 1699409787,
                    '_nanoseconds' => 19825000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => '1c6844fa556b4df7ba67',
                'qty' => '10',
                'name' => 'Free For all',
                'status' => true,
                'sales_end' =>
                array(
                    '_seconds' => 1711800000,
                    '_nanoseconds' => 0,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1711022400,
                    '_nanoseconds' => 0,
                ),
                'updated_at' =>
                array(
                    '_seconds' => 1709159574,
                    '_nanoseconds' => 691087000,
                ),
                'qrcode' => 1709159574,
                'qty_available' => '8',
                'qty_sold' => 2,
            ),
            8 =>
            array(
                'firebase_id' => '4a46f91f2830409c9502',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1717156800,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1714989400,
                'description' => 'testing',
                'created_at' =>
                array(
                    '_seconds' => 1714989400,
                    '_nanoseconds' => 580150000,
                ),
                'type' => 'paid',
                'qty_available' => '10',
                'min_qty_per_order' => '1',
                'event_id' => 'f879a1aee5fd4d3593a5',
                'updated_at' =>
                array(
                    '_seconds' => 1714989400,
                    '_nanoseconds' => 580148000,
                ),
                'price' => '100.00',
                'sales_start' =>
                array(
                    '_seconds' => 1714996800,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'testing',
                'status' => true,
                'qty_sold' => 0,
            ),
            9 =>
            array(
                'firebase_id' => '5cf58aefddd8430eb574',
                'max_qty_per_order' => '3',
                'description' => 'Demo',
                'created_at' =>
                array(
                    '_seconds' => 1697021937,
                    '_nanoseconds' => 814976000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => 'e4eb55c6d74944bfa073',
                'name' => 'demo',
                'status' => true,
                'qty' => '10',
                'no_of_early_bird_economy' => NULL,
                'no_of_tickets_available_vip' => NULL,
                'available_to' => '2024-05-31',
                'early_bird_vip_price' => NULL,
                'no_of_tickets_available_economy' => NULL,
                'title' => 'test',
                'available_from' => '2024-05-07',
                'no_of_early_bird_vip' => NULL,
                'price_economy' => NULL,
                'early_bird_economy_price' => NULL,
                'is_free' => false,
                'price_vip' => NULL,
                'sales_end' =>
                array(
                    '_seconds' => 1719748800,
                    '_nanoseconds' => 0,
                ),
                'updated_at' =>
                array(
                    '_seconds' => 1715056198,
                    '_nanoseconds' => 776283000,
                ),
                'qrcode' => 1715056198,
                'sales_start' =>
                array(
                    '_seconds' => 1715076000,
                    '_nanoseconds' => 0,
                ),
                'qty_available' => 2,
                'qty_sold' => 8,
            ),
            10 =>
            array(
                'firebase_id' => '8da34b25242045589f37',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1708603200,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1708013637,
                'description' => 'Free',
                'created_at' =>
                array(
                    '_seconds' => 1708013637,
                    '_nanoseconds' => 695613000,
                ),
                'type' => 'free',
                'min_qty_per_order' => '1',
                'event_id' => '2d0ba142f2714a918a30',
                'updated_at' =>
                array(
                    '_seconds' => 1708013637,
                    '_nanoseconds' => 695611000,
                ),
                'sales_start' =>
                array(
                    '_seconds' => 1707998400,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'Free',
                'status' => true,
                'qty_sold' => 3,
                'qty_available' => '7',
            ),
            11 =>
            array(
                'firebase_id' => 'ab48b5dfdafb4b7b9b6b',
                'max_qty_per_order' => '10',
                'description' => 'Test',
                'created_at' =>
                array(
                    '_seconds' => 1697021899,
                    '_nanoseconds' => 815705000,
                ),
                'min_qty_per_order' => '1',
                'event_id' => 'e4eb55c6d74944bfa073',
                'qty' => '10',
                'status' => true,
                'type' => 'paid',
                'price' => '10',
                'name' => 'Test paid',
                'sales_end' =>
                array(
                    '_seconds' => 1719748800,
                    '_nanoseconds' => 0,
                ),
                'updated_at' =>
                array(
                    '_seconds' => 1715056223,
                    '_nanoseconds' => 259268000,
                ),
                'qrcode' => 1715056223,
                'sales_start' =>
                array(
                    '_seconds' => 1715076000,
                    '_nanoseconds' => 0,
                ),
                'qty_available' => 7,
                'qty_sold' => 3,
            ),
            12 =>
            array(
                'firebase_id' => 'c9460a0f351c458bb451',
                'max_qty_per_order' => '10',
                'sales_end' =>
                array(
                    '_seconds' => 1719748800,
                    '_nanoseconds' => 0,
                ),
                'qrcode' => 1717496433,
                'description' => 'Paid',
                'created_at' =>
                array(
                    '_seconds' => 1717496433,
                    '_nanoseconds' => 199432000,
                ),
                'type' => 'paid',
                'min_qty_per_order' => '1',
                'event_id' => '4c89ba9652cb4c87999c',
                'updated_at' =>
                array(
                    '_seconds' => 1717496433,
                    '_nanoseconds' => 199431000,
                ),
                'price' => '100.00',
                'sales_start' =>
                array(
                    '_seconds' => 1717502400,
                    '_nanoseconds' => 0,
                ),
                'qty' => '10',
                'name' => 'Paid',
                'status' => true,
                'qty_available' => 8,
                'qty_sold' => 2,
            ),
        );
        foreach ($data as $item) {
            Log::info($item['sales_end']['_seconds']);
            $item['sale_start'] = Carbon::createFromTimestamp($item['sales_start']['_seconds']);
            $item['sale_end'] = Carbon::createFromTimestamp($item['sales_end']['_seconds']);

            $event = LinkUpEvent::where('firebase_id', $item['event_id'])->first();
            $item['event_id'] = $event?->id ?? null;
            $item['quantity'] = $item['qty'] ?? 10;
            $item['status'] = $item['status'] ? 'active' : 'inactive';

            // Clean up old fields not in modern schema
            $validFields = [
                'event_id', 'name', 'type', 'description', 'price',
                'quantity', 'sale_start', 'sale_end', 'status'
            ];

            $cleanData = [];
            foreach ($validFields as $field) {
                if (array_key_exists($field, $item)) {
                    $cleanData[$field] = $item[$field];
                }
            }

            // Find existing by name and event
            $existing = Ticket::where('name', $cleanData['name'])
                ->where('event_id', $cleanData['event_id'])
                ->first();

            if ($existing) {
                $existing->update($cleanData);
            } else {
                Ticket::create($cleanData);
            }
        }
    }
}
