<?php

namespace Database\Seeders;

use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '016e5088e4db4511a068',
                'country' => 'Greece',
                'tax_type' => 'percentage',
                'created_at' => 1670441292000,
                'tax' => '24',
                'updated_at' => 1675860325000,
            ),
            1 =>
            array(
                'firebase_id' => '02988b8e28c1463ba80f',
                'country' => 'Jordan',
                'updated_at' => 1670442476000,
                'tax_type' => 'percentage',
                'created_at' => 1670442476000,
                'tax' => '16',
            ),
            2 =>
            array(
                'firebase_id' => '03badae38da54344a6f8',
                'country' => 'Bermuda',
                'updated_at' => 1670433454000,
                'tax_type' => 'percentage',
                'created_at' => 1670433454000,
                'tax' => '0',
            ),
            3 =>
            array(
                'firebase_id' => '03f5444aa4c040668c31',
                'country' => 'Brazil',
                'updated_at' => 1670433848000,
                'tax_type' => 'percentage',
                'created_at' => 1670433848000,
                'tax' => '16',
            ),
            4 =>
            array(
                'firebase_id' => '0588b066f5834bd19b1b',
                'country' => 'Palau',
                'updated_at' => 1670446476000,
                'tax_type' => 'percentage',
                'created_at' => 1670446476000,
                'tax' => '10',
            ),
            5 =>
            array(
                'firebase_id' => '05948f7589d042cca0e9',
                'country' => 'Bangladesh',
                'updated_at' => 1670432857000,
                'tax_type' => 'percentage',
                'created_at' => 1670432857000,
                'tax' => '12',
            ),
            6 =>
            array(
                'firebase_id' => '077acf9ad6a849f3807c',
                'country' => 'Belgium',
                'updated_at' => 1670433112000,
                'tax_type' => 'percentage',
                'created_at' => 1670433112000,
                'tax' => '21',
            ),
            7 =>
            array(
                'firebase_id' => '0bab5bd96fae4f2581d0',
                'country' => 'Austria',
                'updated_at' => 1670381097000,
                'tax_type' => 'percentage',
                'created_at' => 1670381097000,
                'tax' => '20',
            ),
            8 =>
            array(
                'firebase_id' => '0c96ce1c62d242b494c9',
                'country' => 'Antarctica',
                'updated_at' => 1670380804000,
                'tax_type' => 'percentage',
                'created_at' => 1670380804000,
                'tax' => '0',
            ),
            9 =>
            array(
                'firebase_id' => '0d5285d4d8c141249387',
                'country' => 'Kosovo',
                'updated_at' => 1670442648000,
                'tax_type' => 'percentage',
                'created_at' => 1670442648000,
                'tax' => '18',
            ),
            10 =>
            array(
                'firebase_id' => '0d81bf945cb1491ebfe8',
                'country' => 'Lithuania',
                'updated_at' => 1670443409000,
                'tax_type' => 'percentage',
                'created_at' => 1670443409000,
                'tax' => '21',
            ),
            11 =>
            array(
                'firebase_id' => '0fcfc39b95064b32912a',
                'country' => 'Nepal',
                'updated_at' => 1670445663000,
                'tax_type' => 'percentage',
                'created_at' => 1670445663000,
                'tax' => '13',
            ),
            12 =>
            array(
                'firebase_id' => '107663732317425b8675',
                'country' => 'Latvia',
                'updated_at' => 1670442824000,
                'tax_type' => 'percentage',
                'created_at' => 1670442824000,
                'tax' => '21',
            ),
            13 =>
            array(
                'firebase_id' => '1122fcc874404815a42e',
                'country' => 'Guyana',
                'updated_at' => 1670441681000,
                'tax_type' => 'percentage',
                'created_at' => 1670441681000,
                'tax' => '14',
            ),
            14 =>
            array(
                'firebase_id' => '11c0530b456644d6bcc8',
                'country' => 'Tajikistan',
                'updated_at' => 1670455872000,
                'tax_type' => 'percentage',
                'created_at' => 1670455872000,
                'tax' => '15',
            ),
            15 =>
            array(
                'firebase_id' => '1221bed2bb3c4d80a4ea',
                'country' => 'Bahamas',
                'updated_at' => 1670381182000,
                'tax_type' => 'percentage',
                'created_at' => 1670381182000,
                'tax' => '10',
            ),
            16 =>
            array(
                'firebase_id' => '12e0708db7d94a50b6e7',
                'country' => 'Kenya',
                'updated_at' => 1670442521000,
                'tax_type' => 'percentage',
                'created_at' => 1670442521000,
                'tax' => '16',
            ),
            17 =>
            array(
                'firebase_id' => '13a028de9cc346689682',
                'country' => 'Saint Lucia',
                'updated_at' => 1670447452000,
                'tax_type' => 'percentage',
                'created_at' => 1670447452000,
                'tax' => '12.5',
            ),
            18 =>
            array(
                'firebase_id' => '142ce5a76e7c424c97c8',
                'country' => 'Bonaire, Saint Eustatius and Saba',
                'updated_at' => 1670433638000,
                'tax_type' => 'percentage',
                'created_at' => 1670433638000,
                'tax' => '8',
            ),
            19 =>
            array(
                'firebase_id' => '149d8b10d69b4a598ce1',
                'country' => 'Anguilla',
                'updated_at' => 1670380787000,
                'tax_type' => 'percentage',
                'created_at' => 1670380787000,
                'tax' => '0',
            ),
            20 =>
            array(
                'firebase_id' => '1535edb0435745809323',
                'country' => 'Spain',
                'updated_at' => 1670455310000,
                'tax_type' => 'percentage',
                'created_at' => 1670455310000,
                'tax' => '21',
            ),
            21 =>
            array(
                'firebase_id' => '17863d7595024c56a4c8',
                'country' => 'Chad',
                'updated_at' => 1670434818000,
                'tax_type' => 'percentage',
                'created_at' => 1670434818000,
                'tax' => '18',
            ),
            22 =>
            array(
                'firebase_id' => '1839ca9f3c334f049f9d',
                'country' => 'Niue',
                'updated_at' => 1670446032000,
                'tax_type' => 'percentage',
                'created_at' => 1670446032000,
                'tax' => '12.5',
            ),
            23 =>
            array(
                'firebase_id' => '1b4335ce2597490aa4ce',
                'country' => 'Ecuador',
                'updated_at' => 1670436004000,
                'tax_type' => 'percentage',
                'created_at' => 1670436004000,
                'tax' => '12',
            ),
            24 =>
            array(
                'firebase_id' => '1c104ddd795149219a22',
                'country' => 'Grenada',
                'updated_at' => 1670441404000,
                'tax_type' => 'percentage',
                'created_at' => 1670441404000,
                'tax' => '15',
            ),
            25 =>
            array(
                'firebase_id' => '1ed623ff0de94f4294bf',
                'country' => 'Albania',
                'updated_at' => 1670380604000,
                'tax_type' => 'percentage',
                'created_at' => 1670380604000,
                'tax' => '20',
            ),
            26 =>
            array(
                'firebase_id' => '204ba6ba935b4f569f57',
                'country' => 'Nauru',
                'updated_at' => 1670445593000,
                'tax_type' => 'percentage',
                'created_at' => 1670445593000,
                'tax' => '15',
            ),
            27 =>
            array(
                'firebase_id' => '20926e4cb3374b5b85f2',
                'country' => 'Saudi Arabia',
                'updated_at' => 1670448028000,
                'tax_type' => 'percentage',
                'created_at' => 1670448028000,
                'tax' => '15',
            ),
            28 =>
            array(
                'firebase_id' => '20ec8ec963d24b208b74',
                'country' => 'Kazakhstan',
                'updated_at' => 1670442508000,
                'tax_type' => 'percentage',
                'created_at' => 1670442508000,
                'tax' => '12',
            ),
            29 =>
            array(
                'firebase_id' => '214c4ba678234c43aa33',
                'country' => 'Cayman Islands',
                'updated_at' => 1670434647000,
                'tax_type' => 'percentage',
                'created_at' => 1670434647000,
                'tax' => '0',
            ),
            30 =>
            array(
                'firebase_id' => '21b49c2668224137b3ad',
                'country' => 'Cape Verde',
                'updated_at' => 1670434605000,
                'tax_type' => 'percentage',
                'created_at' => 1670434605000,
                'tax' => '15',
            ),
            31 =>
            array(
                'firebase_id' => '22e2f3f50a904966b49a',
                'country' => 'Puerto Rico',
                'updated_at' => 1670446849000,
                'tax_type' => 'percentage',
                'created_at' => 1670446849000,
                'tax' => '11.5',
            ),
            32 =>
            array(
                'firebase_id' => '253cba4e839c46b3bb83',
                'country' => 'Egypt',
                'updated_at' => 1670436095000,
                'tax_type' => 'percentage',
                'created_at' => 1670436095000,
                'tax' => '14',
            ),
            33 =>
            array(
                'firebase_id' => '26049f5256ce465ab93e',
                'country' => 'Iran',
                'updated_at' => 1670442162000,
                'tax_type' => 'percentage',
                'created_at' => 1670442162000,
                'tax' => '9',
            ),
            34 =>
            array(
                'firebase_id' => '269046806e5d45f19ee8',
                'country' => 'France',
                'updated_at' => 1670440990000,
                'tax_type' => 'percentage',
                'created_at' => 1670440990000,
                'tax' => '20',
            ),
            35 =>
            array(
                'firebase_id' => '26ca80c2169d4d3880bc',
                'country' => 'Mayotte',
                'updated_at' => 1670444582000,
                'tax_type' => 'percentage',
                'created_at' => 1670444582000,
                'tax' => '15',
            ),
            36 =>
            array(
                'firebase_id' => '27334dbe91894ec3a986',
                'country' => 'Argentina',
                'updated_at' => 1670380909000,
                'tax_type' => 'percentage',
                'created_at' => 1670380909000,
                'tax' => '21',
            ),
            37 =>
            array(
                'firebase_id' => '2a96c3a22eba4e16ad35',
                'country' => 'Oman',
                'updated_at' => 1670446360000,
                'tax_type' => 'percentage',
                'created_at' => 1670446360000,
                'tax' => '5',
            ),
            38 =>
            array(
                'firebase_id' => '2bba09965c2f4fd989fa',
                'country' => 'Turkmenistan',
                'updated_at' => 1670456311000,
                'tax_type' => 'percentage',
                'created_at' => 1670456311000,
                'tax' => '15',
            ),
            39 =>
            array(
                'firebase_id' => '2bd133e687ec4fd4a230',
                'country' => 'Croatia',
                'updated_at' => 1670435521000,
                'tax_type' => 'percentage',
                'created_at' => 1670435521000,
                'tax' => '25',
            ),
            40 =>
            array(
                'firebase_id' => '2caa83af1da04051bdc8',
                'country' => 'Azerbaijan',
                'updated_at' => 1670381156000,
                'tax_type' => 'percentage',
                'created_at' => 1670381156000,
                'tax' => '18',
            ),
            41 =>
            array(
                'firebase_id' => '2cd93d0060d74a4882fe',
                'country' => 'Panama',
                'updated_at' => 1670446574000,
                'tax_type' => 'percentage',
                'created_at' => 1670446574000,
                'tax' => '7',
            ),
            42 =>
            array(
                'firebase_id' => '2dc3d591b84e4f40a820',
                'country' => 'Morocco',
                'updated_at' => 1670445132000,
                'tax_type' => 'percentage',
                'created_at' => 1670445132000,
                'tax' => '20',
            ),
            43 =>
            array(
                'firebase_id' => '2e45afb871df4d418467',
                'country' => 'South Sudan',
                'updated_at' => 1670455233000,
                'tax_type' => 'percentage',
                'created_at' => 1670455233000,
                'tax' => '15',
            ),
            44 =>
            array(
                'firebase_id' => '2e5c14bd1ced48c6a81c',
                'country' => 'Dominica',
                'updated_at' => 1670435946000,
                'tax_type' => 'percentage',
                'created_at' => 1670435946000,
                'tax' => '15',
            ),
            45 =>
            array(
                'firebase_id' => '2f206c97c55e4eb69a86',
                'country' => 'Bosnia and Herzegovina',
                'updated_at' => 1670433726000,
                'tax_type' => 'percentage',
                'created_at' => 1670433726000,
                'tax' => '17',
            ),
            46 =>
            array(
                'firebase_id' => '329d564e9c374cf4b272',
                'country' => 'Macedonia',
                'updated_at' => 1670443642000,
                'tax_type' => 'percentage',
                'created_at' => 1670443642000,
                'tax' => '18',
            ),
            47 =>
            array(
                'firebase_id' => '32dccc995f9d454fb555',
                'country' => 'Bulgaria',
                'updated_at' => 1670434128000,
                'tax_type' => 'percentage',
                'created_at' => 1670434128000,
                'tax' => '20',
            ),
            48 =>
            array(
                'firebase_id' => '347aeea170ec40b8a191',
                'country' => 'Cocos (Keeling) Islands',
                'updated_at' => 1670435218000,
                'tax_type' => 'percentage',
                'created_at' => 1670435218000,
                'tax' => '20',
            ),
            49 =>
            array(
                'firebase_id' => '3662cb3f045441a9b517',
                'country' => 'Philippines',
                'updated_at' => 1670446686000,
                'tax_type' => 'percentage',
                'created_at' => 1670446686000,
                'tax' => '12',
            ),
            50 =>
            array(
                'firebase_id' => '37357478caf24e6a9b68',
                'country' => 'Estonia',
                'updated_at' => 1670436281000,
                'tax_type' => 'percentage',
                'created_at' => 1670436281000,
                'tax' => '20',
            ),
            51 =>
            array(
                'firebase_id' => '377f27d6a0e84514b1b9',
                'country' => 'Swaziland',
                'updated_at' => 1670455628000,
                'tax_type' => 'percentage',
                'created_at' => 1670455628000,
                'tax' => '15',
            ),
            52 =>
            array(
                'firebase_id' => '384c37f2b24b46a5aca0',
                'country' => 'Turkey',
                'updated_at' => 1670456262000,
                'tax_type' => 'percentage',
                'created_at' => 1670456262000,
                'tax' => '18',
            ),
            53 =>
            array(
                'firebase_id' => '38aac54ac1fb4ff1ad50',
                'country' => 'Comoros',
                'updated_at' => 1670435354000,
                'tax_type' => 'percentage',
                'created_at' => 1670435354000,
                'tax' => '10',
            ),
            54 =>
            array(
                'firebase_id' => '38f1dfb341834ae3adc5',
                'country' => 'Jamaica',
                'updated_at' => 1670442393000,
                'tax_type' => 'percentage',
                'created_at' => 1670442393000,
                'tax' => '15',
            ),
            55 =>
            array(
                'firebase_id' => '3a00fc874e044a3ba7a1',
                'country' => 'Bahrain',
                'updated_at' => 1670432800000,
                'tax_type' => 'percentage',
                'created_at' => 1670432800000,
                'tax' => '10',
            ),
            56 =>
            array(
                'firebase_id' => '3a165e08e41f44b1838d',
                'country' => 'Lebanon',
                'updated_at' => 1670442839000,
                'tax_type' => 'percentage',
                'created_at' => 1670442839000,
                'tax' => '11',
            ),
            57 =>
            array(
                'firebase_id' => '3a6494477d7943a3a054',
                'country' => 'Ethiopia',
                'updated_at' => 1670436422000,
                'tax_type' => 'percentage',
                'created_at' => 1670436422000,
                'tax' => '15',
            ),
            58 =>
            array(
                'firebase_id' => '3ae112933a854ababe66',
                'country' => 'Canada',
                'updated_at' => 1670434567000,
                'tax_type' => 'percentage',
                'created_at' => 1670434567000,
                'tax' => '15',
            ),
            59 =>
            array(
                'firebase_id' => '3cfeeca200b74770a80d',
                'country' => 'Guam',
                'updated_at' => 1670441507000,
                'tax_type' => 'percentage',
                'created_at' => 1670441507000,
                'tax' => '4',
            ),
            60 =>
            array(
                'firebase_id' => '3d493aad0e484a239b86',
                'country' => 'Nigeria',
                'updated_at' => 1670445979000,
                'tax_type' => 'percentage',
                'created_at' => 1670445979000,
                'tax' => '7.5',
            ),
            61 =>
            array(
                'firebase_id' => '3d761bc2584340ae80be',
                'country' => 'Uruguay',
                'updated_at' => 1670456830000,
                'tax_type' => 'percentage',
                'created_at' => 1670456830000,
                'tax' => '10',
            ),
            62 =>
            array(
                'firebase_id' => '3da6bb9db0b549bcbe1a',
                'country' => 'Samoa',
                'updated_at' => 1670447697000,
                'tax_type' => 'percentage',
                'created_at' => 1670447697000,
                'tax' => '15',
            ),
            63 =>
            array(
                'firebase_id' => '3f5680db23c1464eb9f2',
                'country' => 'Saint Helena',
                'updated_at' => 1670447265000,
                'tax_type' => 'percentage',
                'created_at' => 1670447265000,
                'tax' => '0.5',
            ),
            64 =>
            array(
                'firebase_id' => '3ff2f98b4fc84d9291d5',
                'country' => 'Moldova',
                'updated_at' => 1670444760000,
                'tax_type' => 'percentage',
                'created_at' => 1670444760000,
                'tax' => '20',
            ),
            65 =>
            array(
                'firebase_id' => '400fcf51a3d94129a1b6',
                'country' => 'Djibouti',
                'updated_at' => 1670435880000,
                'tax_type' => 'percentage',
                'created_at' => 1670435880000,
                'tax' => '10',
            ),
            66 =>
            array(
                'firebase_id' => '41500d1422654b1cbe1f',
                'country' => 'Saint Vincent and the Grenadines',
                'updated_at' => 1670447631000,
                'tax_type' => 'percentage',
                'created_at' => 1670447631000,
                'tax' => '16',
            ),
            67 =>
            array(
                'firebase_id' => '44290dfda14740f88d46',
                'country' => 'Singapore',
                'updated_at' => 1670448964000,
                'tax_type' => 'percentage',
                'created_at' => 1670448964000,
                'tax' => '7',
            ),
            68 =>
            array(
                'firebase_id' => '447087d0c068499182cc',
                'country' => 'Ivory Coast',
                'updated_at' => 1670442370000,
                'tax_type' => 'percentage',
                'created_at' => 1670442370000,
                'tax' => '18',
            ),
            69 =>
            array(
                'firebase_id' => '45339347f2354fbb94c4',
                'country' => 'Tanzania',
                'updated_at' => 1670455912000,
                'tax_type' => 'percentage',
                'created_at' => 1670455912000,
                'tax' => '18',
            ),
            70 =>
            array(
                'firebase_id' => '47d3534412f1426bbd15',
                'country' => 'Cook Islands',
                'updated_at' => 1670435449000,
                'tax_type' => 'percentage',
                'created_at' => 1670435449000,
                'tax' => '15',
            ),
            71 =>
            array(
                'firebase_id' => '49181dbc7fe940b39c06',
                'country' => 'Liberia',
                'updated_at' => 1670442946000,
                'tax_type' => 'percentage',
                'created_at' => 1670442946000,
                'tax' => '10',
            ),
            72 =>
            array(
                'firebase_id' => '49dfe3a3edf847dc95db',
                'country' => 'Denmark',
                'updated_at' => 1670435802000,
                'tax_type' => 'percentage',
                'created_at' => 1670435802000,
                'tax' => '25',
            ),
            73 =>
            array(
                'firebase_id' => '49fc3f81850640719944',
                'country' => 'United Kingdom',
                'updated_at' => 1670456602000,
                'tax_type' => 'percentage',
                'created_at' => 1670456602000,
                'tax' => '20',
            ),
            74 =>
            array(
                'firebase_id' => '4a3e5df327b94846994a',
                'country' => 'Tokelau',
                'updated_at' => 1670456107000,
                'tax_type' => 'percentage',
                'created_at' => 1670456107000,
                'tax' => '20',
            ),
            75 =>
            array(
                'firebase_id' => '4bb80d6517434be593fe',
                'country' => 'Barbados',
                'updated_at' => 1670432944000,
                'tax_type' => 'percentage',
                'created_at' => 1670432944000,
                'tax' => '17.5',
            ),
            76 =>
            array(
                'firebase_id' => '4c3a72e25a624ed3b55c',
                'country' => 'Slovakia',
                'updated_at' => 1670449055000,
                'tax_type' => 'percentage',
                'created_at' => 1670449055000,
                'tax' => '20',
            ),
            77 =>
            array(
                'firebase_id' => '4c726af626f041f78892',
                'country' => 'Venezuela',
                'updated_at' => 1670457018000,
                'tax_type' => 'percentage',
                'created_at' => 1670457018000,
                'tax' => '16',
            ),
            78 =>
            array(
                'firebase_id' => '4d8f7e53996d43819dd4',
                'country' => 'Democratic Republic of the Congo',
                'updated_at' => 1670435776000,
                'tax_type' => 'percentage',
                'created_at' => 1670435776000,
                'tax' => '18',
            ),
            79 =>
            array(
                'firebase_id' => '4e32ffd83cf442f98283',
                'country' => 'Saint Pierre and Miquelon',
                'updated_at' => 1670447570000,
                'tax_type' => 'percentage',
                'created_at' => 1670447570000,
                'tax' => '0',
            ),
            80 =>
            array(
                'firebase_id' => '4fcfd2ede4c7484abdf3',
                'country' => 'Costa Rica',
                'updated_at' => 1670435480000,
                'tax_type' => 'percentage',
                'created_at' => 1670435480000,
                'tax' => '13',
            ),
            81 =>
            array(
                'firebase_id' => '4fd3f0820cf74c598a47',
                'country' => 'North Korea',
                'updated_at' => 1670446189000,
                'tax_type' => 'percentage',
                'created_at' => 1670446189000,
                'tax' => '15',
            ),
            82 =>
            array(
                'firebase_id' => '4fe7f7e835eb4dbbba92',
                'country' => 'Aruba',
                'updated_at' => 1670381016000,
                'tax_type' => 'percentage',
                'created_at' => 1670381016000,
                'tax' => '12',
            ),
            83 =>
            array(
                'firebase_id' => '5004399bd72944378549',
                'country' => 'Libya',
                'updated_at' => 1670443254000,
                'tax_type' => 'percentage',
                'created_at' => 1670443254000,
                'tax' => '0',
            ),
            84 =>
            array(
                'firebase_id' => '50a8d2e61aa84af5a937',
                'country' => 'Greenland',
                'updated_at' => 1670441313000,
                'tax_type' => 'percentage',
                'created_at' => 1670441313000,
                'tax' => '0',
            ),
            85 =>
            array(
                'firebase_id' => '511f97d2ce8f419fa234',
                'country' => 'Maldives',
                'updated_at' => 1670443823000,
                'tax_type' => 'percentage',
                'created_at' => 1670443823000,
                'tax' => '6',
            ),
            86 =>
            array(
                'firebase_id' => '517e5b68c81f40f58a90',
                'country' => 'Malawi',
                'updated_at' => 1670443767000,
                'tax_type' => 'percentage',
                'created_at' => 1670443767000,
                'tax' => '16.5',
            ),
            87 =>
            array(
                'firebase_id' => '51a0f99a9de349e497d5',
                'country' => 'Guinea',
                'updated_at' => 1670441632000,
                'tax_type' => 'percentage',
                'created_at' => 1670441632000,
                'tax' => '15',
            ),
            88 =>
            array(
                'firebase_id' => '51e17506e9c346e68a77',
                'country' => 'Ireland',
                'updated_at' => 1670442258000,
                'tax_type' => 'percentage',
                'created_at' => 1670442258000,
                'tax' => '23',
            ),
            89 =>
            array(
                'firebase_id' => '522f99fe3a934eb0b1db',
                'country' => 'Somalia',
                'updated_at' => 1670454778000,
                'tax_type' => 'percentage',
                'created_at' => 1670454778000,
                'tax' => '10',
            ),
            90 =>
            array(
                'firebase_id' => '55717f0155a34ae0b194',
                'country' => 'Bouvet Island',
                'updated_at' => 1670433823000,
                'tax_type' => 'percentage',
                'created_at' => 1670433823000,
                'tax' => '20',
            ),
            91 =>
            array(
                'firebase_id' => '5582b7859da242338ec6',
                'country' => 'Western Sahara',
                'updated_at' => 1670457197000,
                'tax_type' => 'percentage',
                'created_at' => 1670457197000,
                'tax' => '0',
            ),
            92 =>
            array(
                'firebase_id' => '5c1e17191ad94ecabe98',
                'country' => 'Solomon Islands',
                'updated_at' => 1670449148000,
                'tax_type' => 'percentage',
                'created_at' => 1670449148000,
                'tax' => '15',
            ),
            93 =>
            array(
                'firebase_id' => '5d65e681bae340b398d7',
                'country' => 'Suriname',
                'updated_at' => 1670455487000,
                'tax_type' => 'percentage',
                'created_at' => 1670455487000,
                'tax' => '10',
            ),
            94 =>
            array(
                'firebase_id' => '5dd36099b428432ab6f0',
                'country' => 'Belize',
                'updated_at' => 1670433186000,
                'tax_type' => 'percentage',
                'created_at' => 1670433186000,
                'tax' => '12.5',
            ),
            95 =>
            array(
                'firebase_id' => '5ec45f4f2e144936aa3d',
                'country' => 'India',
                'updated_at' => 1670442083000,
                'tax_type' => 'percentage',
                'created_at' => 1670442083000,
                'tax' => '28',
            ),
            96 =>
            array(
                'firebase_id' => '5fe60d4dc688453887b2',
                'country' => 'Falkland Islands',
                'updated_at' => 1670436495000,
                'tax_type' => 'percentage',
                'created_at' => 1670436495000,
                'tax' => '0',
            ),
            97 =>
            array(
                'firebase_id' => '606178a907634469a184',
                'country' => 'Malta',
                'updated_at' => 1670443918000,
                'tax_type' => 'percentage',
                'created_at' => 1670443918000,
                'tax' => '18',
            ),
            98 =>
            array(
                'firebase_id' => '61bcc49b2c8a49dea29a',
                'country' => 'Vanuatu',
                'updated_at' => 1670456898000,
                'tax_type' => 'percentage',
                'created_at' => 1670456898000,
                'tax' => '12.5',
            ),
            99 =>
            array(
                'firebase_id' => '62235ea1fec04d668b65',
                'country' => 'Norway',
                'updated_at' => 1670446327000,
                'tax_type' => 'percentage',
                'created_at' => 1670446327000,
                'tax' => '25',
            ),
            100 =>
            array(
                'firebase_id' => '6276adcb94994b538680',
                'country' => 'Sierra Leone',
                'updated_at' => 1670448428000,
                'tax_type' => 'percentage',
                'created_at' => 1670448428000,
                'tax' => '15',
            ),
            101 =>
            array(
                'firebase_id' => '67d7ee02b2c346f694c0',
                'country' => 'Svalbard and Jan Mayen',
                'updated_at' => 1670455573000,
                'tax_type' => 'percentage',
                'created_at' => 1670455573000,
                'tax' => '0',
            ),
            102 =>
            array(
                'firebase_id' => '68943fb172974b27b3a9',
                'country' => 'Peru',
                'updated_at' => 1670446664000,
                'tax_type' => 'percentage',
                'created_at' => 1670446664000,
                'tax' => '18',
            ),
            103 =>
            array(
                'firebase_id' => '6a8522fbfea14bc7b260',
                'country' => 'Andorra',
                'updated_at' => 1670380682000,
                'tax_type' => 'percentage',
                'created_at' => 1670380682000,
                'tax' => '0',
            ),
            104 =>
            array(
                'firebase_id' => '6d9967490b614f9b9f71',
                'country' => 'Israel',
                'updated_at' => 1670442311000,
                'tax_type' => 'percentage',
                'created_at' => 1670442311000,
                'tax' => '17',
            ),
            105 =>
            array(
                'firebase_id' => '6f9e718f54e14b13b23d',
                'country' => 'Finland',
                'updated_at' => 1670440962000,
                'tax_type' => 'percentage',
                'created_at' => 1670440962000,
                'tax' => '24',
            ),
            106 =>
            array(
                'firebase_id' => '6ff80954c1014b03b8ad',
                'country' => 'Romania',
                'updated_at' => 1670446953000,
                'tax_type' => 'percentage',
                'created_at' => 1670446953000,
                'tax' => '19',
            ),
            107 =>
            array(
                'firebase_id' => '721487ea14ae4c8f9194',
                'country' => 'Togo',
                'updated_at' => 1670456027000,
                'tax_type' => 'percentage',
                'created_at' => 1670456027000,
                'tax' => '18',
            ),
            108 =>
            array(
                'firebase_id' => '725ed9f127f2492592c6',
                'country' => 'Ukraine',
                'updated_at' => 1670456552000,
                'tax_type' => 'percentage',
                'created_at' => 1670456552000,
                'tax' => '20',
            ),
            109 =>
            array(
                'firebase_id' => '7301611742944293962a',
                'country' => 'Kiribati',
                'updated_at' => 1670442623000,
                'tax_type' => 'percentage',
                'created_at' => 1670442623000,
                'tax' => '0',
            ),
            110 =>
            array(
                'firebase_id' => '7302589ec41b44b38c21',
                'country' => 'Thailand',
                'updated_at' => 1670455949000,
                'tax_type' => 'percentage',
                'created_at' => 1670455949000,
                'tax' => '7',
            ),
            111 =>
            array(
                'firebase_id' => '739f05a19c114beabad2',
                'country' => 'Gibraltar',
                'updated_at' => 1670441267000,
                'tax_type' => 'percentage',
                'created_at' => 1670441267000,
                'tax' => '0',
            ),
            112 =>
            array(
                'firebase_id' => '7597a7bdbe104e9d81cd',
                'country' => 'Equatorial Guinea',
                'updated_at' => 1670436158000,
                'tax_type' => 'percentage',
                'created_at' => 1670436158000,
                'tax' => '15',
            ),
            113 =>
            array(
                'firebase_id' => '76c24f632837440fbe60',
                'country' => 'Netherlands',
                'updated_at' => 1670445701000,
                'tax_type' => 'percentage',
                'created_at' => 1670445701000,
                'tax' => '21',
            ),
            114 =>
            array(
                'firebase_id' => '78d019dc439e402bb437',
                'country' => 'Congo',
                'updated_at' => 1670435403000,
                'tax_type' => 'percentage',
                'created_at' => 1670435403000,
                'tax' => '18',
            ),
            115 =>
            array(
                'firebase_id' => '79ff8aea73c8491b9459',
                'country' => 'New Zealand',
                'updated_at' => 1670445802000,
                'tax_type' => 'percentage',
                'created_at' => 1670445802000,
                'tax' => '15',
            ),
            116 =>
            array(
                'firebase_id' => '7a846769e79d4b8592ea',
                'country' => 'Marshall Islands',
                'updated_at' => 1670443978000,
                'tax_type' => 'percentage',
                'created_at' => 1670443978000,
                'tax' => '4',
            ),
            117 =>
            array(
                'firebase_id' => '7c0d7ee672aa4a75bd4d',
                'country' => 'Northern Mariana Islands',
                'updated_at' => 1670446291000,
                'tax_type' => 'percentage',
                'created_at' => 1670446291000,
                'tax' => '0',
            ),
            118 =>
            array(
                'firebase_id' => '7c4584d283224578b82d',
                'country' => 'Afghanistan',
                'updated_at' => 1675246682000,
                'tax_type' => 'percentage',
                'created_at' => 1675246682000,
                'tax' => '0',
            ),
            119 =>
            array(
                'firebase_id' => '7d5d5464b7dc486285fc',
                'country' => 'Curacao',
                'updated_at' => 1670435623000,
                'tax_type' => 'percentage',
                'created_at' => 1670435623000,
                'tax' => '6',
            ),
            120 =>
            array(
                'firebase_id' => '7de48bb5b0384445bdad',
                'country' => 'Czech Republic',
                'updated_at' => 1670435683000,
                'tax_type' => 'percentage',
                'created_at' => 1670435683000,
                'tax' => '21',
            ),
            121 =>
            array(
                'firebase_id' => '8121d580206a44ce9fdf',
                'country' => 'Haiti',
                'updated_at' => 1670441732000,
                'tax_type' => 'percentage',
                'created_at' => 1670441732000,
                'tax' => '10',
            ),
            122 =>
            array(
                'firebase_id' => '81a771addda54d3b9c12',
                'country' => 'Vatican City State (Holy See)',
                'updated_at' => 1670456989000,
                'tax_type' => 'percentage',
                'created_at' => 1670456989000,
                'tax' => '22',
            ),
            123 =>
            array(
                'firebase_id' => '83682b28a07a47f9a3e6',
                'country' => 'South Georgia and the South Sandwich Islands',
                'updated_at' => 1670454984000,
                'tax_type' => 'percentage',
                'created_at' => 1670454984000,
                'tax' => '7',
            ),
            124 =>
            array(
                'firebase_id' => '844c3397338f49b4a079',
                'country' => 'Laos',
                'updated_at' => 1670442790000,
                'tax_type' => 'percentage',
                'created_at' => 1670442790000,
                'tax' => '10',
            ),
            125 =>
            array(
                'firebase_id' => '86905bff5fe04fe5b725',
                'country' => 'Uganda',
                'updated_at' => 1670456531000,
                'tax_type' => 'percentage',
                'created_at' => 1670456531000,
                'tax' => '18',
            ),
            126 =>
            array(
                'firebase_id' => '86ec43e11d7247ecb0fe',
                'country' => 'Heard Island and McDonald Islands',
                'updated_at' => 1670441836000,
                'tax_type' => 'percentage',
                'created_at' => 1670441836000,
                'tax' => '0',
            ),
            127 =>
            array(
                'firebase_id' => '8746f38867ce4235acf4',
                'country' => 'Réunion',
                'updated_at' => 1670447121000,
                'tax_type' => 'percentage',
                'created_at' => 1670447121000,
                'tax' => '8.5',
            ),
            128 =>
            array(
                'firebase_id' => '88dfac0ffb6444f9baed',
                'country' => 'Jersey',
                'updated_at' => 1670442452000,
                'tax_type' => 'percentage',
                'created_at' => 1670442452000,
                'tax' => '5',
            ),
            129 =>
            array(
                'firebase_id' => '89f172f3302a4de5bd82',
                'country' => 'United Arab Emirates',
                'updated_at' => 1670456587000,
                'tax_type' => 'percentage',
                'created_at' => 1670456587000,
                'tax' => '5',
            ),
            130 =>
            array(
                'firebase_id' => '8af4003750664b319486',
                'country' => 'Myanmar',
                'updated_at' => 1670445305000,
                'tax_type' => 'percentage',
                'created_at' => 1670445305000,
                'tax' => '0',
            ),
            131 =>
            array(
                'firebase_id' => '8afcc89c616e4aedbdcd',
                'country' => 'Mozambique',
                'updated_at' => 1670445173000,
                'tax_type' => 'percentage',
                'created_at' => 1670445173000,
                'tax' => '17',
            ),
            132 =>
            array(
                'firebase_id' => '8ba8a6c04f4c407f9f93',
                'country' => 'Indonesia',
                'updated_at' => 1670442105000,
                'tax_type' => 'percentage',
                'created_at' => 1670442105000,
                'tax' => '11',
            ),
            133 =>
            array(
                'firebase_id' => '8e8d1439aaf04d64865d',
                'country' => 'Falkland Islands',
                'updated_at' => 1670440850000,
                'tax_type' => 'percentage',
                'created_at' => 1670440850000,
                'tax' => '0',
            ),
            134 =>
            array(
                'firebase_id' => '938f023644c24a7fb23e',
                'country' => 'Tunisia',
                'updated_at' => 1670456220000,
                'tax_type' => 'percentage',
                'created_at' => 1670456220000,
                'tax' => '19',
            ),
            135 =>
            array(
                'firebase_id' => '93e0822e5c3148db8c1f',
                'country' => 'Micronesia',
                'updated_at' => 1670444709000,
                'tax_type' => 'percentage',
                'created_at' => 1670444709000,
                'tax' => '5',
            ),
            136 =>
            array(
                'firebase_id' => '943533a6b0fd4f2b922f',
                'country' => 'Norfolk Island',
                'updated_at' => 1670446111000,
                'tax_type' => 'percentage',
                'created_at' => 1670446111000,
                'tax' => '12',
            ),
            137 =>
            array(
                'firebase_id' => '94573504c04d4d368275',
                'country' => 'Japan',
                'updated_at' => 1670442434000,
                'tax_type' => 'percentage',
                'created_at' => 1670442434000,
                'tax' => '10',
            ),
            138 =>
            array(
                'firebase_id' => '9458a4164a7a425489cc',
                'country' => 'Italy',
                'updated_at' => 1670442333000,
                'tax_type' => 'percentage',
                'created_at' => 1670442333000,
                'tax' => '22',
            ),
            139 =>
            array(
                'firebase_id' => '94badc656e3d4f70829d',
                'country' => 'British Virgin Islands',
                'updated_at' => 1670433989000,
                'tax_type' => 'percentage',
                'created_at' => 1670433989000,
                'tax' => '0',
            ),
            140 =>
            array(
                'firebase_id' => '95a2df6e042045a79671',
                'country' => 'Luxembourg',
                'updated_at' => 1670443535000,
                'tax_type' => 'percentage',
                'created_at' => 1670443535000,
                'tax' => '17',
            ),
            141 =>
            array(
                'firebase_id' => '9615f122f4e74dc7a98b',
                'country' => 'Yemen',
                'updated_at' => 1670457265000,
                'tax_type' => 'percentage',
                'created_at' => 1670457265000,
                'tax' => '5',
            ),
            142 =>
            array(
                'firebase_id' => '96ad54cd2ea94c379118',
                'country' => 'Monaco',
                'updated_at' => 1670444825000,
                'tax_type' => 'percentage',
                'created_at' => 1670444825000,
                'tax' => '20',
            ),
            143 =>
            array(
                'firebase_id' => '9a3ccb7aa8d647e0a6ef',
                'country' => 'Kyrgyzstan',
                'updated_at' => 1670442773000,
                'tax_type' => 'percentage',
                'created_at' => 1670442773000,
                'tax' => '12',
            ),
            144 =>
            array(
                'firebase_id' => '9a5a7ba8bd1b41448213',
                'country' => 'Uzbekistan',
                'updated_at' => 1670456857000,
                'tax_type' => 'percentage',
                'created_at' => 1670456857000,
                'tax' => '15',
            ),
            145 =>
            array(
                'firebase_id' => '9ca88fb9db2d4f53b565',
                'country' => 'Russia',
                'updated_at' => 1670447021000,
                'tax_type' => 'percentage',
                'created_at' => 1670447021000,
                'tax' => '16.67',
            ),
            146 =>
            array(
                'firebase_id' => '9d886d5992574b20b051',
                'country' => 'Australia',
                'updated_at' => 1670381061000,
                'tax_type' => 'percentage',
                'created_at' => 1670381061000,
                'tax' => '10',
            ),
            147 =>
            array(
                'firebase_id' => '9eef7e2e18994b09b905',
                'country' => 'Serbia',
                'updated_at' => 1670448288000,
                'tax_type' => 'percentage',
                'created_at' => 1670448288000,
                'tax' => '20',
            ),
            148 =>
            array(
                'firebase_id' => '9f138014e9c14879a780',
                'country' => 'Hong Kong',
                'updated_at' => 1670441910000,
                'tax_type' => 'percentage',
                'created_at' => 1670441910000,
                'tax' => '0',
            ),
            149 =>
            array(
                'firebase_id' => '9f604971f32740a88c76',
                'country' => 'Aland Islands',
                'updated_at' => 1670380577000,
                'tax_type' => 'percentage',
                'created_at' => 1670380577000,
                'tax' => '0',
            ),
            150 =>
            array(
                'firebase_id' => '9fa5daca8a98476c94d6',
                'country' => 'Macau',
                'updated_at' => 1670443573000,
                'tax_type' => 'percentage',
                'created_at' => 1670443573000,
                'tax' => '0',
            ),
            151 =>
            array(
                'firebase_id' => 'a22d7eda0b974007b696',
                'country' => 'Pitcairn',
                'updated_at' => 1670446750000,
                'tax_type' => 'percentage',
                'created_at' => 1670446750000,
                'tax' => '0',
            ),
            152 =>
            array(
                'firebase_id' => 'a308f7434a9e4295846a',
                'country' => 'Burundi',
                'updated_at' => 1670434382000,
                'tax_type' => 'percentage',
                'created_at' => 1670434382000,
                'tax' => '18',
            ),
            153 =>
            array(
                'firebase_id' => 'a42846f828da41fe9d83',
                'country' => 'Botswana',
                'updated_at' => 1670433759000,
                'tax_type' => 'percentage',
                'created_at' => 1670433759000,
                'tax' => '12',
            ),
            154 =>
            array(
                'firebase_id' => 'a4581206d9f04bbe86aa',
                'country' => 'Mongolia',
                'updated_at' => 1670444925000,
                'tax_type' => 'percentage',
                'created_at' => 1670444925000,
                'tax' => '10',
            ),
            155 =>
            array(
                'firebase_id' => 'a47c30b5cff744aab0d3',
                'country' => 'South Korea',
                'updated_at' => 1670455104000,
                'tax_type' => 'percentage',
                'created_at' => 1670455104000,
                'tax' => '10',
            ),
            156 =>
            array(
                'firebase_id' => 'a5299b4a6a8e426aabb4',
                'country' => 'San Marino',
                'updated_at' => 1670447791000,
                'tax_type' => 'percentage',
                'created_at' => 1670447791000,
                'tax' => '17',
            ),
            157 =>
            array(
                'firebase_id' => 'a534a9ea343c45c8857b',
                'country' => 'Dominican Republic',
                'updated_at' => 1670435977000,
                'tax_type' => 'percentage',
                'created_at' => 1670435977000,
                'tax' => '18',
            ),
            158 =>
            array(
                'firebase_id' => 'a5e3e9d07cf84bc0bdfe',
                'country' => 'Cyprus',
                'updated_at' => 1670435658000,
                'tax_type' => 'percentage',
                'created_at' => 1670435658000,
                'tax' => '19',
            ),
            159 =>
            array(
                'firebase_id' => 'a66c5ee4b2984a96851c',
                'country' => 'U.S. Virgin Islands',
                'updated_at' => 1670456504000,
                'tax_type' => 'percentage',
                'created_at' => 1670456504000,
                'tax' => '20',
            ),
            160 =>
            array(
                'firebase_id' => 'a7729ebcf4b148b589f4',
                'country' => 'Turks and Caicos Islands',
                'updated_at' => 1670456364000,
                'tax_type' => 'percentage',
                'created_at' => 1670456364000,
                'tax' => '0',
            ),
            161 =>
            array(
                'firebase_id' => 'a928dd3cf9674aefbd26',
                'country' => 'Senegal',
                'updated_at' => 1670448152000,
                'tax_type' => 'percentage',
                'created_at' => 1670448152000,
                'tax' => '18',
            ),
            162 =>
            array(
                'firebase_id' => 'a9785a7e395d4b9dbac2',
                'country' => 'Faroe Islands',
                'updated_at' => 1670440882000,
                'tax_type' => 'percentage',
                'created_at' => 1670440882000,
                'tax' => '25',
            ),
            163 =>
            array(
                'firebase_id' => 'a9b955482ad74a748902',
                'country' => 'Sao Tome and Principe',
                'updated_at' => 1670447913000,
                'tax_type' => 'percentage',
                'created_at' => 1670447913000,
                'tax' => '17',
            ),
            164 =>
            array(
                'firebase_id' => 'a9c7b86f35d44fda8108',
                'country' => 'Timor-Leste',
                'updated_at' => 1670455990000,
                'tax_type' => 'percentage',
                'created_at' => 1670455990000,
                'tax' => '0',
            ),
            165 =>
            array(
                'firebase_id' => 'acf3a113bcb942e3ac2c',
                'country' => 'Qatar',
                'updated_at' => 1670446890000,
                'tax_type' => 'percentage',
                'created_at' => 1670446890000,
                'tax' => '0',
            ),
            166 =>
            array(
                'firebase_id' => 'acf91515b3494c04a9d9',
                'country' => 'Cambodia',
                'updated_at' => 1670434416000,
                'tax_type' => 'percentage',
                'created_at' => 1670434416000,
                'tax' => '10',
            ),
            167 =>
            array(
                'firebase_id' => 'adf846af9fef44fe81b6',
                'country' => 'Sweden',
                'updated_at' => 1670455680000,
                'tax_type' => 'percentage',
                'created_at' => 1670455680000,
                'tax' => '25',
            ),
            168 =>
            array(
                'firebase_id' => 'ae349a906af54663a5ed',
                'country' => 'Saint Barthelemy',
                'updated_at' => 1670447174000,
                'tax_type' => 'percentage',
                'created_at' => 1670447174000,
                'tax' => '0',
            ),
            169 =>
            array(
                'firebase_id' => 'afacf487702e4f5b9336',
                'country' => 'Brunei',
                'updated_at' => 1670434099000,
                'tax_type' => 'percentage',
                'created_at' => 1670434099000,
                'tax' => '18',
            ),
            170 =>
            array(
                'firebase_id' => 'afb9c1212cb7448fa397',
                'country' => 'Guinea-Bissau',
                'updated_at' => 1670441650000,
                'tax_type' => 'percentage',
                'created_at' => 1670441650000,
                'tax' => '20',
            ),
            171 =>
            array(
                'firebase_id' => 'b10e87ba1975435ab29a',
                'country' => 'Zambia',
                'updated_at' => 1670457289000,
                'tax_type' => 'percentage',
                'created_at' => 1670457289000,
                'tax' => '16',
            ),
            172 =>
            array(
                'firebase_id' => 'b1e793a39b1a436288cb',
                'country' => 'Nicaragua',
                'updated_at' => 1670445898000,
                'tax_type' => 'percentage',
                'created_at' => 1670445898000,
                'tax' => '15',
            ),
            173 =>
            array(
                'firebase_id' => 'b4154e9be3dd43f3bbc6',
                'country' => 'Lesotho',
                'updated_at' => 1670442897000,
                'tax_type' => 'percentage',
                'created_at' => 1670442897000,
                'tax' => '15',
            ),
            174 =>
            array(
                'firebase_id' => 'b4d62c346d2c4d3c9327',
                'country' => 'Mexico',
                'updated_at' => 1670444610000,
                'tax_type' => 'percentage',
                'created_at' => 1670444610000,
                'tax' => '16',
            ),
            175 =>
            array(
                'firebase_id' => 'b6465b52938f4c478a50',
                'country' => 'Niger',
                'updated_at' => 1670445948000,
                'tax_type' => 'percentage',
                'created_at' => 1670445948000,
                'tax' => '7.5',
            ),
            176 =>
            array(
                'firebase_id' => 'b725233a8dba46a7b836',
                'country' => 'Belarus',
                'updated_at' => 1670433080000,
                'tax_type' => 'percentage',
                'created_at' => 1670433080000,
                'tax' => '20',
            ),
            177 =>
            array(
                'firebase_id' => 'b777495a4f1d4b43b5dc',
                'country' => 'Isle of Man',
                'updated_at' => 1670442282000,
                'tax_type' => 'percentage',
                'created_at' => 1670442282000,
                'tax' => '20',
            ),
            178 =>
            array(
                'firebase_id' => 'b77cb8fa2f5049448d34',
                'country' => 'Germany',
                'updated_at' => 1670441185000,
                'tax_type' => 'percentage',
                'created_at' => 1670441185000,
                'tax' => '19',
            ),
            179 =>
            array(
                'firebase_id' => 'b873ba1828b6430cb2f9',
                'country' => 'Mauritania',
                'updated_at' => 1670444238000,
                'tax_type' => 'percentage',
                'created_at' => 1670444238000,
                'tax' => '16',
            ),
            180 =>
            array(
                'firebase_id' => 'b8f96009a7db4a82854f',
                'country' => 'Sudan',
                'updated_at' => 1670455400000,
                'tax_type' => 'percentage',
                'created_at' => 1670455400000,
                'tax' => '17',
            ),
            181 =>
            array(
                'firebase_id' => 'bc7c2cbf25bf46e3b82c',
                'country' => 'Hungary',
                'updated_at' => 1670441946000,
                'tax_type' => 'percentage',
                'created_at' => 1670441946000,
                'tax' => '27',
            ),
            182 =>
            array(
                'firebase_id' => 'be9b9f49916141819d43',
                'country' => 'Sint Maarten',
                'updated_at' => 1670449029000,
                'tax_type' => 'percentage',
                'created_at' => 1670449029000,
                'tax' => '5',
            ),
            183 =>
            array(
                'firebase_id' => 'bf2bb4e4af7847549004',
                'country' => 'Paraguay',
                'updated_at' => 1670446638000,
                'tax_type' => 'percentage',
                'created_at' => 1670446638000,
                'tax' => '10',
            ),
            184 =>
            array(
                'firebase_id' => 'bf6fc65599804f5d9da3',
                'country' => 'United States',
                'updated_at' => 1670456647000,
                'tax_type' => 'percentage',
                'created_at' => 1670456647000,
                'tax' => '6',
            ),
            185 =>
            array(
                'firebase_id' => 'bf7379a9d3c54169aaba',
                'country' => 'Iraq',
                'updated_at' => 1670442226000,
                'tax_type' => 'percentage',
                'created_at' => 1670442226000,
                'tax' => '0',
            ),
            186 =>
            array(
                'firebase_id' => 'c086735857ce45118687',
                'country' => 'Angola',
                'updated_at' => 1670380705000,
                'tax_type' => 'percentage',
                'created_at' => 1670380705000,
                'tax' => '14',
            ),
            187 =>
            array(
                'firebase_id' => 'c0e80188f02942079ba9',
                'country' => 'Montserrat',
                'updated_at' => 1670445096000,
                'tax_type' => 'percentage',
                'created_at' => 1670445096000,
                'tax' => '10',
            ),
            188 =>
            array(
                'firebase_id' => 'c0eb1e57191e48b38f5d',
                'country' => 'Georgia',
                'updated_at' => 1670441143000,
                'tax_type' => 'percentage',
                'created_at' => 1670441143000,
                'tax' => '18',
            ),
            189 =>
            array(
                'firebase_id' => 'c1e010e3cfd84db89640',
                'country' => 'Trinidad and Tobago',
                'updated_at' => 1670456186000,
                'tax_type' => 'percentage',
                'created_at' => 1670456186000,
                'tax' => '12.5',
            ),
            190 =>
            array(
                'firebase_id' => 'c1ed300b19534c4586e4',
                'country' => 'Antigua and Barbuda',
                'updated_at' => 1670380868000,
                'tax_type' => 'percentage',
                'created_at' => 1670380868000,
                'tax' => '15',
            ),
            191 =>
            array(
                'firebase_id' => 'c2eb3bb422de48fbbb86',
                'country' => 'Rwanda',
                'updated_at' => 1670447069000,
                'tax_type' => 'percentage',
                'created_at' => 1670447069000,
                'tax' => '18',
            ),
            192 =>
            array(
                'firebase_id' => 'c36740e821694fada5e1',
                'country' => 'Saint Kitts and Nevis',
                'updated_at' => 1670447360000,
                'tax_type' => 'percentage',
                'created_at' => 1670447360000,
                'tax' => '17',
            ),
            193 =>
            array(
                'firebase_id' => 'c4196b9fd1a34add8ef1',
                'country' => 'Chile',
                'updated_at' => 1670434852000,
                'tax_type' => 'percentage',
                'created_at' => 1670434852000,
                'tax' => '19',
            ),
            194 =>
            array(
                'firebase_id' => 'c41d1601c7e64e0e859b',
                'country' => 'Namibia',
                'updated_at' => 1670445373000,
                'tax_type' => 'percentage',
                'created_at' => 1670445373000,
                'tax' => '15',
            ),
            195 =>
            array(
                'firebase_id' => 'c461b2a41db348539aa3',
                'country' => 'Malaysia',
                'updated_at' => 1670443799000,
                'tax_type' => 'percentage',
                'created_at' => 1670443799000,
                'tax' => '10',
            ),
            196 =>
            array(
                'firebase_id' => 'c46c1e47aea44259ac39',
                'country' => 'Burkina Faso',
                'updated_at' => 1670434266000,
                'tax_type' => 'percentage',
                'created_at' => 1670434266000,
                'tax' => '12',
            ),
            197 =>
            array(
                'firebase_id' => 'c5075c854f0e4d218a1b',
                'country' => 'Pakistan',
                'updated_at' => 1670446408000,
                'tax_type' => 'percentage',
                'created_at' => 1670446408000,
                'tax' => '16',
            ),
            198 =>
            array(
                'firebase_id' => 'c5638677ca854ba1a9f2',
                'country' => 'Bolivia',
                'updated_at' => 1670433571000,
                'tax_type' => 'percentage',
                'created_at' => 1670433571000,
                'tax' => '13',
            ),
            199 =>
            array(
                'firebase_id' => 'c962e7a6638848f9a4a3',
                'country' => 'French Polynesia',
                'updated_at' => 1670441039000,
                'tax_type' => 'percentage',
                'created_at' => 1670441039000,
                'tax' => '6',
            ),
            200 =>
            array(
                'firebase_id' => 'ca090db0f64a4c96a725',
                'country' => 'Kuwait',
                'updated_at' => 1670442725000,
                'tax_type' => 'percentage',
                'created_at' => 1670442725000,
                'tax' => '0',
            ),
            201 =>
            array(
                'firebase_id' => 'cb35fd2894ca49bea945',
                'country' => 'Iceland',
                'updated_at' => 1670442037000,
                'tax_type' => 'percentage',
                'created_at' => 1670442037000,
                'tax' => '24',
            ),
            202 =>
            array(
                'firebase_id' => 'cd6b2ec88e354457a907',
                'country' => 'Saint Martin',
                'updated_at' => 1670447492000,
                'tax_type' => 'percentage',
                'created_at' => 1670447492000,
                'tax' => '5',
            ),
            203 =>
            array(
                'firebase_id' => 'ceee8cf60e8c4e8289be',
                'country' => 'Madagascar',
                'updated_at' => 1670443682000,
                'tax_type' => 'percentage',
                'created_at' => 1670443682000,
                'tax' => '20',
            ),
            204 =>
            array(
                'firebase_id' => 'cfa925187c754dee95be',
                'country' => 'Vietnam',
                'updated_at' => 1670457043000,
                'tax_type' => 'percentage',
                'created_at' => 1670457043000,
                'tax' => '10',
            ),
            205 =>
            array(
                'firebase_id' => 'd0a5507b0ca94f0ab3df',
                'country' => 'French Southern Territories',
                'updated_at' => 1670441104000,
                'tax_type' => 'percentage',
                'created_at' => 1670441104000,
                'tax' => '20',
            ),
            206 =>
            array(
                'firebase_id' => 'd0c4af446df843c0ad83',
                'country' => 'Central African Republic',
                'updated_at' => 1670434784000,
                'tax_type' => 'percentage',
                'created_at' => 1670434784000,
                'tax' => '19',
            ),
            207 =>
            array(
                'firebase_id' => 'd0cb87e714ae486a9828',
                'country' => 'New Caledonia',
                'updated_at' => 1670445771000,
                'tax_type' => 'percentage',
                'created_at' => 1670445771000,
                'tax' => '6',
            ),
            208 =>
            array(
                'firebase_id' => 'd1e019e282224be191b2',
                'country' => 'American Samoa',
                'updated_at' => 1670380659000,
                'tax_type' => 'percentage',
                'created_at' => 1670380659000,
                'tax' => '0',
            ),
            209 =>
            array(
                'firebase_id' => 'd35489611f8149c8b770',
                'country' => 'Gabon',
                'updated_at' => 1670441127000,
                'tax_type' => 'percentage',
                'created_at' => 1670441127000,
                'tax' => '18',
            ),
            210 =>
            array(
                'firebase_id' => 'd4fb2c741e3940ada4f4',
                'country' => 'Eritrea',
                'updated_at' => 1670436234000,
                'tax_type' => 'percentage',
                'created_at' => 1670436234000,
                'tax' => '12',
            ),
            211 =>
            array(
                'firebase_id' => 'd595e71d80014e5f8f51',
                'country' => 'Ghana',
                'updated_at' => 1670441206000,
                'tax_type' => 'percentage',
                'created_at' => 1670441206000,
                'tax' => '12.5',
            ),
            212 =>
            array(
                'firebase_id' => 'd5c584643fdc4b188349',
                'country' => 'Afghanistan',
                'updated_at' => 1670380544000,
                'tax_type' => 'percentage',
                'created_at' => 1670380544000,
                'tax' => '0',
            ),
            213 =>
            array(
                'firebase_id' => 'd776ff3ae57944628460',
                'country' => 'Guatemala',
                'updated_at' => 1670441539000,
                'tax_type' => 'percentage',
                'created_at' => 1670441539000,
                'tax' => '12',
            ),
            214 =>
            array(
                'firebase_id' => 'd804bec92f63416ca5f1',
                'country' => 'Fiji',
                'updated_at' => 1670440911000,
                'tax_type' => 'percentage',
                'created_at' => 1670440911000,
                'tax' => '15',
            ),
            215 =>
            array(
                'firebase_id' => 'd8be5537871943aca29b',
                'country' => 'Colombia',
                'updated_at' => 1670435309000,
                'tax_type' => 'percentage',
                'created_at' => 1670435309000,
                'tax' => '19',
            ),
            216 =>
            array(
                'firebase_id' => 'd9863a4da6f841e893d4',
                'country' => 'Bhutan',
                'updated_at' => 1670433541000,
                'tax_type' => 'percentage',
                'created_at' => 1670433541000,
                'tax' => '18',
            ),
            217 =>
            array(
                'firebase_id' => 'da4b27e381a34038bcc7',
                'country' => 'Cuba',
                'updated_at' => 1670435574000,
                'tax_type' => 'percentage',
                'created_at' => 1670435574000,
                'tax' => '15',
            ),
            218 =>
            array(
                'firebase_id' => 'db387f7596fc4e83b402',
                'country' => 'Tonga',
                'updated_at' => 1670456153000,
                'tax_type' => 'percentage',
                'created_at' => 1670456153000,
                'tax' => '15',
            ),
            219 =>
            array(
                'firebase_id' => 'db7db275f7bf43d6a5e0',
                'country' => 'Papua New Guinea',
                'updated_at' => 1670446598000,
                'tax_type' => 'percentage',
                'created_at' => 1670446598000,
                'tax' => '10',
            ),
            220 =>
            array(
                'firebase_id' => 'dbb2d92523dc497b8619',
                'country' => 'Montenegro',
                'updated_at' => 1670444953000,
                'tax_type' => 'percentage',
                'created_at' => 1670444953000,
                'tax' => '21',
            ),
            221 =>
            array(
                'firebase_id' => 'dc14bbc2f6544d4e97d5',
                'country' => 'El Salvador',
                'updated_at' => 1670436127000,
                'tax_type' => 'percentage',
                'created_at' => 1670436127000,
                'tax' => '13',
            ),
            222 =>
            array(
                'firebase_id' => 'dd06f50477254456aaed',
                'country' => 'South Africa',
                'updated_at' => 1670454850000,
                'tax_type' => 'percentage',
                'created_at' => 1670454850000,
                'tax' => '15',
            ),
            223 =>
            array(
                'firebase_id' => 'df7c0359502944839b68',
                'country' => 'Guernsey',
                'updated_at' => 1675246571000,
                'tax_type' => 'percentage',
                'created_at' => 1675246571000,
                'tax' => '0',
            ),
            224 =>
            array(
                'firebase_id' => 'dfd3ca30543045789863',
                'country' => 'Tuvalu',
                'updated_at' => 1670456454000,
                'tax_type' => 'percentage',
                'created_at' => 1670456454000,
                'tax' => '7',
            ),
            225 =>
            array(
                'firebase_id' => 'e1310fa09ff945f699e0',
                'country' => 'Cocos Islands',
                'updated_at' => 1670435253000,
                'tax_type' => 'percentage',
                'created_at' => 1670435253000,
                'tax' => '20',
            ),
            226 =>
            array(
                'firebase_id' => 'e1a274102e874bb6bef9',
                'country' => 'Cameroon',
                'updated_at' => 1670434459000,
                'tax_type' => 'percentage',
                'created_at' => 1670434459000,
                'tax' => '19.25',
            ),
            227 =>
            array(
                'firebase_id' => 'e254d048b41c4de5a773',
                'country' => 'China',
                'updated_at' => 1670435004000,
                'tax_type' => 'percentage',
                'created_at' => 1670435004000,
                'tax' => '13',
            ),
            228 =>
            array(
                'firebase_id' => 'e26e6fc68536466f8a39',
                'country' => 'Poland',
                'updated_at' => 1670446780000,
                'tax_type' => 'percentage',
                'created_at' => 1670446780000,
                'tax' => '23',
            ),
            229 =>
            array(
                'firebase_id' => 'e2b3499e56e94fa8a809',
                'country' => 'Mali',
                'updated_at' => 1670443888000,
                'tax_type' => 'percentage',
                'created_at' => 1670443888000,
                'tax' => '18',
            ),
            230 =>
            array(
                'firebase_id' => 'e30ce9bc9ef046439d78',
                'country' => 'Liechtenstein',
                'updated_at' => 1670443334000,
                'tax_type' => 'percentage',
                'created_at' => 1670443334000,
                'tax' => '7.7',
            ),
            231 =>
            array(
                'firebase_id' => 'e40852b110e644588759',
                'country' => 'Taiwan',
                'updated_at' => 1670455821000,
                'tax_type' => 'percentage',
                'created_at' => 1670455821000,
                'tax' => '5',
            ),
            232 =>
            array(
                'firebase_id' => 'e565189925f8444ca044',
                'country' => 'British Indian Ocean Territory',
                'updated_at' => 1670433931000,
                'tax_type' => 'percentage',
                'created_at' => 1670433931000,
                'tax' => '20',
            ),
            233 =>
            array(
                'firebase_id' => 'e6779bd60f054ed3933f',
                'country' => 'Sri Lanka',
                'updated_at' => 1670455334000,
                'tax_type' => 'percentage',
                'created_at' => 1670455334000,
                'tax' => '8',
            ),
            234 =>
            array(
                'firebase_id' => 'e701d602d70f4102b080',
                'country' => 'Wallis and Futuna',
                'updated_at' => 1670457114000,
                'tax_type' => 'percentage',
                'created_at' => 1670457114000,
                'tax' => '0',
            ),
            235 =>
            array(
                'firebase_id' => 'e935521021c84ddaaa8b',
                'country' => 'Algeria',
                'updated_at' => 1670380628000,
                'tax_type' => 'percentage',
                'created_at' => 1670380628000,
                'tax' => '19',
            ),
            236 =>
            array(
                'firebase_id' => 'ea400603325a4d46b150',
                'country' => 'Palestinian Territory',
                'updated_at' => 1670446529000,
                'tax_type' => 'percentage',
                'created_at' => 1670446529000,
                'tax' => '16',
            ),
            237 =>
            array(
                'firebase_id' => 'eab5a8fa493d4fef8ea9',
                'country' => 'Luxembourg',
                'updated_at' => 1670443431000,
                'tax_type' => 'percentage',
                'created_at' => 1670443431000,
                'tax' => '17',
            ),
            238 =>
            array(
                'firebase_id' => 'eda5c63927cb48938390',
                'country' => 'Martinique',
                'updated_at' => 1670444101000,
                'tax_type' => 'percentage',
                'created_at' => 1670444101000,
                'tax' => '8.5',
            ),
            239 =>
            array(
                'firebase_id' => 'ee36e680d2a643a7bd75',
                'country' => 'Honduras',
                'updated_at' => 1670441861000,
                'tax_type' => 'percentage',
                'created_at' => 1670441861000,
                'tax' => '15',
            ),
            240 =>
            array(
                'firebase_id' => 'f04bf4f4f3834dd396b2',
                'country' => 'Vatican',
                'updated_at' => 1670456958000,
                'tax_type' => 'percentage',
                'created_at' => 1670456958000,
                'tax' => '22',
            ),
            241 =>
            array(
                'firebase_id' => 'f179b8f52acc41e0b849',
                'country' => 'Armenia',
                'updated_at' => 1670380951000,
                'tax_type' => 'percentage',
                'created_at' => 1670380951000,
                'tax' => '20',
            ),
            242 =>
            array(
                'firebase_id' => 'f44f4d13b1bf40099fbb',
                'country' => 'Zimbabwe',
                'updated_at' => 1670457314000,
                'tax_type' => 'percentage',
                'created_at' => 1670457314000,
                'tax' => '14.5',
            ),
            243 =>
            array(
                'firebase_id' => 'f5559d6111184351bf70',
                'country' => 'Benin',
                'updated_at' => 1670433361000,
                'tax_type' => 'percentage',
                'created_at' => 1670433361000,
                'tax' => '10',
            ),
            244 =>
            array(
                'firebase_id' => 'f5d11f56b0004f2eaac6',
                'country' => 'Mauritius',
                'updated_at' => 1670444376000,
                'tax_type' => 'percentage',
                'created_at' => 1670444376000,
                'tax' => '15',
            ),
            245 =>
            array(
                'firebase_id' => 'f614b55e82cc4d86b510',
                'country' => 'Christmas Island',
                'updated_at' => 1670435087000,
                'tax_type' => 'percentage',
                'created_at' => 1670435087000,
                'tax' => '20',
            ),
            246 =>
            array(
                'firebase_id' => 'f703383874254e23a6ad',
                'country' => 'Slovenia',
                'updated_at' => 1670449078000,
                'tax_type' => 'percentage',
                'created_at' => 1670449078000,
                'tax' => '22',
            ),
            247 =>
            array(
                'firebase_id' => 'f8e037039e0044328f94',
                'country' => 'Portugal',
                'updated_at' => 1670446821000,
                'tax_type' => 'percentage',
                'created_at' => 1670446821000,
                'tax' => '23',
            ),
            248 =>
            array(
                'firebase_id' => 'f93aa5c100144204b34f',
                'country' => 'Switzerland',
                'updated_at' => 1670455716000,
                'tax_type' => 'percentage',
                'created_at' => 1670455716000,
                'tax' => '7.7',
            ),
            249 =>
            array(
                'firebase_id' => 'fa8f20855ce440408f9d',
                'country' => 'Syria',
                'updated_at' => 1670455790000,
                'tax_type' => 'percentage',
                'created_at' => 1670455790000,
                'tax' => '0',
            ),
            250 =>
            array(
                'firebase_id' => 'fbb7c5f5127c4613ac45',
                'country' => 'Guadeloupe',
                'updated_at' => 1670441449000,
                'tax_type' => 'percentage',
                'created_at' => 1670441449000,
                'tax' => '8.5',
            ),
            251 =>
            array(
                'firebase_id' => 'fc3d0ad79d6a426b9d42',
                'country' => 'Seychelles',
                'updated_at' => 1670448333000,
                'tax_type' => 'percentage',
                'created_at' => 1670448333000,
                'tax' => '15',
            ),
            252 =>
            array(
                'firebase_id' => 'fd9d710e78c64c45a28f',
                'country' => 'Madagascar',
                'updated_at' => 1670443733000,
                'tax_type' => 'percentage',
                'created_at' => 1670443733000,
                'tax' => '20',
            ),
            253 =>
            array(
                'firebase_id' => 'fe9b900cedf347a38417',
                'country' => 'Guernsey',
                'updated_at' => 1670441570000,
                'tax_type' => 'percentage',
                'created_at' => 1670441570000,
                'tax' => '0',
            ),
        );

        foreach ($data as $item) {
            $item['created_at'] = Carbon::createFromTimestamp($item['created_at'] / 1000);
            $item['updated_at'] = Carbon::createFromTimestamp($item['updated_at'] / 1000);
            Tax::updateOrCreate(
                ['firebase_id' => $item['firebase_id']],
                $item
            );
        }
    }
}
