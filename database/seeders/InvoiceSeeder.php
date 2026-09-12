<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '17ca8563e59a4346b005',
                'notes' => 'test',
                'from_email' => 'hello@example.com',
                'for_name' => 'BTC',
                'from_phone' => '324324234234234234',
                'created_at' => 1672308369,
                'for_address' => 'Bay, , New Providence, Bahamas',
                'from_name' => 'Linkup',
                'doc_id' => 'c1a7087591a64fb3b8d3',
                'for_email' => 'email@email.com',
                'sequence' => 5,
                'updated_at' => 1672308369,
                'for_phone' => '242-3454333',
                'from_address' => 'islamabad',
                'collection_name' => 'ads',
            ),
            1 =>
            array(
                'firebase_id' => '1e4066eeb7fe4020929c',
                'for_name' => 'test restaurant 3',
                'notes' => 'test notes',
                'from_email' => 'hello@example.com',
                'from_phone' => '324324234234234234',
                'for_address' => 'islamabad pakistan, Alhambra, Arizona, United States',
                'created_at' => 1672121257,
                'from_name' => 'Linkup',
                'for_email' => 'test@gmail.com',
                'doc_id' => 'a525bea693a7486eab3a',
                'sequence' => 3,
                'updated_at' => 1672121257,
                'for_phone' => '23423423423',
                'from_address' => 'islamabad',
                'collection_name' => 'restaurants',
            ),
            2 =>
            array(
                'firebase_id' => '23bd0fb4ee89409aa09a',
                'notes' => 'test',
                'from_email' => 'hello@example.com',
                'for_name' => 'Test club/Fete',
                'from_phone' => '324324234234234234',
                'for_address' => 'islamabad pakistan, Miami, Florida, United States',
                'created_at' => 1672308186,
                'from_name' => 'Linkup',
                'doc_id' => '5fcbf1196cca415bacde',
                'for_email' => 'test3@gmail.com',
                'sequence' => 4,
                'updated_at' => 1672308186,
                'for_phone' => '23423423423',
                'from_address' => 'Test',
                'collection_name' => 'clubsFetes',
            ),
            3 =>
            array(
                'firebase_id' => '3ab1991ac0be4fba94a6',
                'notes' => NULL,
                'for_name' => 'test',
                'from_email' => 'hello@example.com',
                'from_phone' => NULL,
                'created_at' => 1672765648,
                'for_address' => 'public_html, Rwp, , Afghanistan',
                'from_name' => 'Linkup',
                'doc_id' => '6669272fba5c44089992',
                'for_email' => 'wanikhan806@yahoo.com',
                'sequence' => 8,
                'updated_at' => 1672765648,
                'for_phone' => '0345488787',
                'from_address' => NULL,
                'collection_name' => 'clubsFetes',
            ),
            4 =>
            array(
                'firebase_id' => '6994f4c340544d6daffb',
                'from_email' => 'hello@example.com',
                'for_name' => 'Brezzers',
                'notes' => NULL,
                'from_phone' => NULL,
                'for_address' => 'Bay Street, , New Providence, Bahamas',
                'created_at' => 1683494565,
                'from_name' => 'Linkup',
                'doc_id' => '56e802d0414c4b6abbf0',
                'for_email' => 'cstuart@caribmedltd.com',
                'sequence' => 10,
                'updated_at' => 1683494565,
                'for_phone' => '2424226374',
                'from_address' => NULL,
                'collection_name' => 'ads',
            ),
            5 =>
            array(
                'firebase_id' => '9784c221915c42b1ac66',
                'from_email' => 'hello@example.com',
                'for_name' => 'test',
                'notes' => 'test',
                'from_phone' => 'test',
                'for_address' => 'test, Nassau, New Providence, The Bahamas',
                'created_at' => 1686545224,
                'from_name' => 'Linkup',
                'doc_id' => '25c3c3c31e0c4445b09f',
                'for_email' => 'test@test.com',
                'sequence' => 11,
                'updated_at' => 1686545224,
                'for_phone' => '44444',
                'from_address' => 'test',
                'collection_name' => 'restaurants',
            ),
            6 =>
            array(
                'firebase_id' => '9e1e8eb7ed9b4974be22',
                'for_name' => 'Test Restaurant 2',
                'from_email' => 'hello@example.com',
                'notes' => 'test notes',
                'from_phone' => '324324234234234234',
                'for_address' => 'islamabad pakistan, Chaman, Balochistan, Pakistan',
                'created_at' => 1672118210,
                'from_name' => 'Linkup',
                'for_email' => 'test3@gmail.com',
                'doc_id' => '189b36452e82474fb616',
                'sequence' => 1,
                'updated_at' => 1672118210,
                'for_phone' => '23423423423',
                'from_address' => 'islamabad',
                'collection_name' => 'restaurants',
            ),
            7 =>
            array(
                'firebase_id' => 'af5f739c796642a3ade9',
                'for_name' => 'Test Restaurant 1',
                'notes' => 'test notes',
                'from_email' => 'hello@example.com',
                'from_phone' => '324324234234234234',
                'for_address' => 'islamabad pakistan, Ghormach, Badghis, Afghanistan',
                'created_at' => 1672118689,
                'from_name' => 'Linkup',
                'doc_id' => 'a27993b3eae44224acee',
                'for_email' => 'test@gmail.com',
                'sequence' => 2,
                'updated_at' => 1672118689,
                'for_phone' => '23423423423',
                'from_address' => 'islamabad',
                'collection_name' => 'restaurants',
            ),
            8 =>
            array(
                'firebase_id' => 'afd7ebd231054f19827d',
                'for_name' => 'kakil Beer',
                'notes' => NULL,
                'from_email' => 'hello@example.com',
                'from_phone' => NULL,
                'created_at' => 1672362048,
                'for_address' => ', , New Providence, Bahamas',
                'from_name' => 'Linkup',
                'for_email' => 'cstuart@caribmedltd.com',
                'doc_id' => '226d1b2cef2f47e3afc4',
                'sequence' => 6,
                'updated_at' => 1672362048,
                'for_phone' => '242-3432-3433',
                'from_address' => NULL,
                'collection_name' => 'ads',
            ),
            9 =>
            array(
                'firebase_id' => 'b596232963e54536b2d5',
                'notes' => 'The you for you business.',
                'from_email' => 'hello@example.com',
                'for_name' => 'Breezer Hotel',
                'from_phone' => '243233432233',
                'created_at' => 1673220166,
                'for_address' => 'West Bay Street, , New Providence, Bahamas',
                'from_name' => 'Linkup',
                'for_email' => 'email@email.com',
                'doc_id' => '4bf1958746f34db9b264',
                'sequence' => 9,
                'updated_at' => 1673220166,
                'for_phone' => '242-3432-3433',
                'from_address' => '2222 nW 10th Miami,Flordia',
                'collection_name' => 'ads',
            ),
            10 =>
            array(
                'firebase_id' => 'fcadd4900b3542e1b6a3',
                'from_email' => 'hello@example.com',
                'notes' => 'Thank you for your business.',
                'for_name' => 'Island Luck',
                'from_phone' => '786-3954140',
                'created_at' => 1672705665,
                'for_address' => 'Bay Sterret, , New Providence, Bahamas',
                'from_name' => 'Linkup',
                'doc_id' => '306eaa1954ed43e998a7',
                'for_email' => 'email@email.com',
                'sequence' => 7,
                'updated_at' => 1672705665,
                'for_phone' => '242-3432-3433',
                'from_address' => '305 New 24 Street,',
                'collection_name' => 'ads',
            ),
        );
        foreach ($data as $item) {
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at']);
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at']);
            Invoice::updateOrCreate(
                 ['firebase_id' => $item['firebase_id']],
                 $item
             );
        }
    }
}
