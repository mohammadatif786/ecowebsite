<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'id' => '1',
                'country' => 'US Dollar',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array(
                'id' => '2',
                'country' => 'Canadian Dollar',
                'currency_code' => 'CAD',
                'currency_symbol' => 'CA$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 =>
            array(
                'id' => '3',
                'country' => 'Euro',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 =>
            array(
                'id' => '4',
                'country' => 'United Arab Emirates Dirham',
                'currency_code' => 'AED',
                'currency_symbol' => 'د.إ.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 =>
            array(
                'id' => '5',
                'country' => 'Afghan Afghani',
                'currency_code' => 'AFN',
                'currency_symbol' => '؋',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 =>
            array(
                'id' => '6',
                'country' => 'Albanian Lek',
                'currency_code' => 'ALL',
                'currency_symbol' => 'L',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 =>
            array(
                'id' => '7',
                'country' => 'Armenian Dram',
                'currency_code' => 'AMD',
                'currency_symbol' => '֏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 =>
            array(
                'id' => '8',
                'country' => 'Argentine Peso',
                'currency_code' => 'ARS',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 =>
            array(
                'id' => '9',
                'country' => 'Australian Dollar',
                'currency_code' => 'AUD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            9 =>
            array(
                'id' => '10',
                'country' => 'Azerbaijani Manat',
                'currency_code' => 'AZN',
                'currency_symbol' => '₼',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            10 =>
            array(
                'id' => '11',
                'country' => 'Bosnia-Herzegovina Convertible Mark',
                'currency_code' => 'BAM',
                'currency_symbol' => 'KM',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            11 =>
            array(
                'id' => '12',
                'country' => 'Bangladeshi Taka',
                'currency_code' => 'BDT',
                'currency_symbol' => '৳',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            12 =>
            array(
                'id' => '13',
                'country' => 'Bulgarian Lev',
                'currency_code' => 'BGN',
                'currency_symbol' => 'лв.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            13 =>
            array(
                'id' => '14',
                'country' => 'Bahraini Dinar',
                'currency_code' => 'BHD',
                'currency_symbol' => 'د.ب.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            14 =>
            array(
                'id' => '15',
                'country' => 'Burundian Franc',
                'currency_code' => 'BIF',
                'currency_symbol' => 'FBu',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            15 =>
            array(
                'id' => '16',
                'country' => 'Brunei Dollar',
                'currency_code' => 'BND',
                'currency_symbol' => 'B$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            16 =>
            array(
                'id' => '17',
                'country' => 'Bolivian Boliviano',
                'currency_code' => 'BOB',
                'currency_symbol' => 'Bs',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            17 =>
            array(
                'id' => '18',
                'country' => 'Brazilian Real',
                'currency_code' => 'BRL',
                'currency_symbol' => 'R$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            18 =>
            array(
                'id' => '19',
                'country' => 'Botswanan Pula',
                'currency_code' => 'BWP',
                'currency_symbol' => 'P',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            19 =>
            array(
                'id' => '20',
                'country' => 'Belarusian Ruble',
                'currency_code' => 'BYN',
                'currency_symbol' => 'Br',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 =>
            array(
                'id' => '21',
                'country' => 'Belize Dollar',
                'currency_code' => 'BZD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            21 =>
            array(
                'id' => '22',
                'country' => 'Congolese Franc',
                'currency_code' => 'CDF',
                'currency_symbol' => 'FC',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            22 =>
            array(
                'id' => '23',
                'country' => 'Swiss Franc',
                'currency_code' => 'CHF',
                'currency_symbol' => 'CHf',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            23 =>
            array(
                'id' => '24',
                'country' => 'Chilean Peso',
                'currency_code' => 'CLP',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            24 =>
            array(
                'id' => '25',
                'country' => 'Chinese Yuan',
                'currency_code' => 'CNY',
                'currency_symbol' => '¥',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            25 =>
            array(
                'id' => '26',
                'country' => 'Colombian Peso',
                'currency_code' => 'COP',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            26 =>
            array(
                'id' => '27',
                'country' => 'Costa Rican Colón',
                'currency_code' => 'CRC',
                'currency_symbol' => '₡',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            27 =>
            array(
                'id' => '28',
                'country' => 'Cape Verdean Escudo',
                'currency_code' => 'CVE',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            28 =>
            array(
                'id' => '29',
                'country' => 'Czech Republic Koruna',
                'currency_code' => 'CZK',
                'currency_symbol' => 'Kč',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            29 =>
            array(
                'id' => '30',
                'country' => 'Djiboutian Franc',
                'currency_code' => 'DJF',
                'currency_symbol' => 'Fdj',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            30 =>
            array(
                'id' => '31',
                'country' => 'Danish Krone',
                'currency_code' => 'DKK',
                'currency_symbol' => 'Kr.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            31 =>
            array(
                'id' => '32',
                'country' => 'Dominican Peso',
                'currency_code' => 'DOP',
                'currency_symbol' => 'RD$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            32 =>
            array(
                'id' => '33',
                'country' => 'Algerian Dinar',
                'currency_code' => 'DZD',
                'currency_symbol' => 'د.ج.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            33 =>
            array(
                'id' => '34',
                'country' => 'Estonian Kroon',
                'currency_code' => 'EEK',
                'currency_symbol' => 'kr',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            34 =>
            array(
                'id' => '35',
                'country' => 'Egyptian Pound',
                'currency_code' => 'EGP',
                'currency_symbol' => 'E£‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            35 =>
            array(
                'id' => '36',
                'country' => 'Eritrean Nakfa',
                'currency_code' => 'ERN',
                'currency_symbol' => 'Nfk',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            36 =>
            array(
                'id' => '37',
                'country' => 'Ethiopian Birr',
                'currency_code' => 'ETB',
                'currency_symbol' => 'Br',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            37 =>
            array(
                'id' => '38',
                'country' => 'British Pound Sterling',
                'currency_code' => 'GBP',
                'currency_symbol' => '£',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            38 =>
            array(
                'id' => '39',
                'country' => 'Georgian Lari',
                'currency_code' => 'GEL',
                'currency_symbol' => 'GEL',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            39 =>
            array(
                'id' => '40',
                'country' => 'Ghanaian Cedi',
                'currency_code' => 'GHS',
                'currency_symbol' => 'GH¢',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            40 =>
            array(
                'id' => '41',
                'country' => 'Guinean Franc',
                'currency_code' => 'GNF',
                'currency_symbol' => 'FG',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            41 =>
            array(
                'id' => '42',
                'country' => 'Guatemalan Quetzal',
                'currency_code' => 'GTQ',
                'currency_symbol' => 'Q',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            42 =>
            array(
                'id' => '43',
                'country' => 'Hong Kong Dollar',
                'currency_code' => 'HKD',
                'currency_symbol' => 'HK$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            43 =>
            array(
                'id' => '44',
                'country' => 'Honduran Lempira',
                'currency_code' => 'HNL',
                'currency_symbol' => 'L',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            44 =>
            array(
                'id' => '45',
                'country' => 'Croatian Kuna',
                'currency_code' => 'HRK',
                'currency_symbol' => 'kn',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            45 =>
            array(
                'id' => '46',
                'country' => 'Hungarian Forint',
                'currency_code' => 'HUF',
                'currency_symbol' => 'Ft',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            46 =>
            array(
                'id' => '47',
                'country' => 'Indonesian Rupiah',
                'currency_code' => 'IDR',
                'currency_symbol' => 'Rp',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            47 =>
            array(
                'id' => '48',
                'country' => 'Israeli New Sheqel',
                'currency_code' => 'ILS',
                'currency_symbol' => '₪',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            48 =>
            array(
                'id' => '49',
                'country' => 'Indian Rupee',
                'currency_code' => 'INR',
                'currency_symbol' => '₹',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            49 =>
            array(
                'id' => '50',
                'country' => 'Iraqi Dinar',
                'currency_code' => 'IQD',
                'currency_symbol' => 'ع.د',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            50 =>
            array(
                'id' => '51',
                'country' => 'Iranian Rial',
                'currency_code' => 'IRR',
                'currency_symbol' => '﷼',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            51 =>
            array(
                'id' => '52',
                'country' => 'Icelandic Króna',
                'currency_code' => 'ISK',
                'currency_symbol' => 'kr',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            52 =>
            array(
                'id' => '53',
                'country' => 'Jamaican Dollar',
                'currency_code' => 'JMD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            53 =>
            array(
                'id' => '54',
                'country' => 'Jordanian Dinar',
                'currency_code' => 'JOD',
                'currency_symbol' => 'د.ا‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            54 =>
            array(
                'id' => '55',
                'country' => 'Japanese Yen',
                'currency_code' => 'JPY',
                'currency_symbol' => '¥',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            55 =>
            array(
                'id' => '56',
                'country' => 'Kenyan Shilling',
                'currency_code' => 'KES',
                'currency_symbol' => 'Ksh',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            56 =>
            array(
                'id' => '57',
                'country' => 'Cambodian Riel',
                'currency_code' => 'KHR',
                'currency_symbol' => '៛',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            57 =>
            array(
                'id' => '58',
                'country' => 'Comorian Franc',
                'currency_code' => 'KMF',
                'currency_symbol' => 'FC',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            58 =>
            array(
                'id' => '59',
                'country' => 'South Korean Won',
                'currency_code' => 'KRW',
                'currency_symbol' => 'CF',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            59 =>
            array(
                'id' => '60',
                'country' => 'Kuwaiti Dinar',
                'currency_code' => 'KWD',
                'currency_symbol' => 'د.ك.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            60 =>
            array(
                'id' => '61',
                'country' => 'Kazakhstani Tenge',
                'currency_code' => 'KZT',
                'currency_symbol' => '₸.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            61 =>
            array(
                'id' => '62',
                'country' => 'Lebanese Pound',
                'currency_code' => 'LBP',
                'currency_symbol' => 'ل.ل.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            62 =>
            array(
                'id' => '63',
                'country' => 'Sri Lankan Rupee',
                'currency_code' => 'LKR',
                'currency_symbol' => 'Rs',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            63 =>
            array(
                'id' => '64',
                'country' => 'Lithuanian Litas',
                'currency_code' => 'LTL',
                'currency_symbol' => 'Lt',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            64 =>
            array(
                'id' => '65',
                'country' => 'Latvian Lats',
                'currency_code' => 'LVL',
                'currency_symbol' => 'Ls',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            65 =>
            array(
                'id' => '66',
                'country' => 'Libyan Dinar',
                'currency_code' => 'LYD',
                'currency_symbol' => 'د.ل.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            66 =>
            array(
                'id' => '67',
                'country' => 'Moroccan Dirham',
                'currency_code' => 'MAD',
                'currency_symbol' => 'د.م.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            67 =>
            array(
                'id' => '68',
                'country' => 'Moldovan Leu',
                'currency_code' => 'MDL',
                'currency_symbol' => 'L',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            68 =>
            array(
                'id' => '69',
                'country' => 'Malagasy Ariary',
                'currency_code' => 'MGA',
                'currency_symbol' => 'Ar',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            69 =>
            array(
                'id' => '70',
                'country' => 'Macedonian Denar',
                'currency_code' => 'MKD',
                'currency_symbol' => 'Ден',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            70 =>
            array(
                'id' => '71',
                'country' => 'Myanma Kyat',
                'currency_code' => 'MMK',
                'currency_symbol' => 'K',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            71 =>
            array(
                'id' => '72',
                'country' => 'Macanese Pataca',
                'currency_code' => 'MOP',
                'currency_symbol' => 'MOP$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            72 =>
            array(
                'id' => '73',
                'country' => 'Mauritian Rupee',
                'currency_code' => 'MUR',
                'currency_symbol' => 'Rs',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            73 =>
            array(
                'id' => '74',
                'country' => 'Mexican Peso',
                'currency_code' => 'MXN',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            74 =>
            array(
                'id' => '75',
                'country' => 'Malaysian Ringgit',
                'currency_code' => 'MYR',
                'currency_symbol' => 'RM',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            75 =>
            array(
                'id' => '76',
                'country' => 'Mozambican Metical',
                'currency_code' => 'MZN',
                'currency_symbol' => 'MT',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            76 =>
            array(
                'id' => '77',
                'country' => 'Namibian Dollar',
                'currency_code' => 'NAD',
                'currency_symbol' => 'N$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            77 =>
            array(
                'id' => '78',
                'country' => 'Nigerian Naira',
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            78 =>
            array(
                'id' => '79',
                'country' => 'Nicaraguan Córdoba',
                'currency_code' => 'NIO',
                'currency_symbol' => 'C$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            79 =>
            array(
                'id' => '80',
                'country' => 'Norwegian Krone',
                'currency_code' => 'NOK',
                'currency_symbol' => 'kr',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            80 =>
            array(
                'id' => '81',
                'country' => 'Nepalese Rupee',
                'currency_code' => 'NPR',
                'currency_symbol' => 'Re.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            81 =>
            array(
                'id' => '82',
                'country' => 'New Zealand Dollar',
                'currency_code' => 'NZD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            82 =>
            array(
                'id' => '83',
                'country' => 'Omani Rial',
                'currency_code' => 'OMR',
                'currency_symbol' => 'ر.ع.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            83 =>
            array(
                'id' => '84',
                'country' => 'Panamanian Balboa',
                'currency_code' => 'PAB',
                'currency_symbol' => 'B/.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            84 =>
            array(
                'id' => '85',
                'country' => 'Peruvian Nuevo Sol',
                'currency_code' => 'PEN',
                'currency_symbol' => 'S/',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            85 =>
            array(
                'id' => '86',
                'country' => 'Philippine Peso',
                'currency_code' => 'PHP',
                'currency_symbol' => '₱',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            86 =>
            array(
                'id' => '87',
                'country' => 'Pakistani Rupee',
                'currency_code' => 'PKR',
                'currency_symbol' => 'Rs',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            87 =>
            array(
                'id' => '88',
                'country' => 'Polish Zloty',
                'currency_code' => 'PLN',
                'currency_symbol' => 'zł',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            88 =>
            array(
                'id' => '89',
                'country' => 'Paraguayan Guarani',
                'currency_code' => 'PYG',
                'currency_symbol' => '₲',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            89 =>
            array(
                'id' => '90',
                'country' => 'Qatari Rial',
                'currency_code' => 'QAR',
                'currency_symbol' => 'ر.ق.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            90 =>
            array(
                'id' => '91',
                'country' => 'Romanian Leu',
                'currency_code' => 'RON',
                'currency_symbol' => 'lei',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            91 =>
            array(
                'id' => '92',
                'country' => 'Serbian Dinar',
                'currency_code' => 'RSD',
                'currency_symbol' => 'din.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            92 =>
            array(
                'id' => '93',
                'country' => 'Russian Ruble',
                'currency_code' => 'RUB',
                'currency_symbol' => '₽.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            93 =>
            array(
                'id' => '94',
                'country' => 'Rwandan Franc',
                'currency_code' => 'RWF',
                'currency_symbol' => 'FRw',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            94 =>
            array(
                'id' => '95',
                'country' => 'Saudi Riyal',
                'currency_code' => 'SAR',
                'currency_symbol' => 'ر.س.‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            95 =>
            array(
                'id' => '96',
                'country' => 'Sudanese Pound',
                'currency_code' => 'SDG',
                'currency_symbol' => 'ج.س.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            96 =>
            array(
                'id' => '97',
                'country' => 'Swedish Krona',
                'currency_code' => 'SEK',
                'currency_symbol' => 'kr',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            97 =>
            array(
                'id' => '98',
                'country' => 'Singapore Dollar',
                'currency_code' => 'SGD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            98 =>
            array(
                'id' => '99',
                'country' => 'Somali Shilling',
                'currency_code' => 'SOS',
                'currency_symbol' => 'Sh.so.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            99 =>
            array(
                'id' => '100',
                'country' => 'Syrian Pound',
                'currency_code' => 'SYP',
                'currency_symbol' => 'LS‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            100 =>
            array(
                'id' => '101',
                'country' => 'Thai Baht',
                'currency_code' => 'THB',
                'currency_symbol' => '฿',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            101 =>
            array(
                'id' => '102',
                'country' => 'Tunisian Dinar',
                'currency_code' => 'TND',
                'currency_symbol' => 'د.ت‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            102 =>
            array(
                'id' => '103',
                'country' => 'Tongan Paʻanga',
                'currency_code' => 'TOP',
                'currency_symbol' => 'T$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            103 =>
            array(
                'id' => '104',
                'country' => 'Turkish Lira',
                'currency_code' => 'TRY',
                'currency_symbol' => '₺',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            104 =>
            array(
                'id' => '105',
                'country' => 'Trinidad and Tobago Dollar',
                'currency_code' => 'TTD',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            105 =>
            array(
                'id' => '106',
                'country' => 'New Taiwan Dollar',
                'currency_code' => 'TWD',
                'currency_symbol' => 'NT$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            106 =>
            array(
                'id' => '107',
                'country' => 'Tanzanian Shilling',
                'currency_code' => 'TZS',
                'currency_symbol' => 'TSh',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            107 =>
            array(
                'id' => '108',
                'country' => 'Ukrainian Hryvnia',
                'currency_code' => 'UAH',
                'currency_symbol' => '₴',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            108 =>
            array(
                'id' => '109',
                'country' => 'Ugandan Shilling',
                'currency_code' => 'UGX',
                'currency_symbol' => 'USh',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            109 =>
            array(
                'id' => '110',
                'country' => 'Uruguayan Peso',
                'currency_code' => 'UYU',
                'currency_symbol' => '$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            110 =>
            array(
                'id' => '111',
                'country' => 'Uzbekistan Som',
                'currency_code' => 'UZS',
                'currency_symbol' => 'so\'m',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            111 =>
            array(
                'id' => '112',
                'country' => 'Venezuelan Bolívar',
                'currency_code' => 'VEF',
                'currency_symbol' => 'Bs.F.',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            112 =>
            array(
                'id' => '113',
                'country' => 'Vietnamese Dong',
                'currency_code' => 'VND',
                'currency_symbol' => '₫',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            113 =>
            array(
                'id' => '114',
                'country' => 'CFA Franc BEAC',
                'currency_code' => 'XAF',
                'currency_symbol' => 'FCFA',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            114 =>
            array(
                'id' => '115',
                'country' => 'CFA Franc BCEAO',
                'currency_code' => 'XOF',
                'currency_symbol' => 'CFA',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            115 =>
            array(
                'id' => '116',
                'country' => 'Yemeni Rial',
                'currency_code' => 'YER',
                'currency_symbol' => '﷼‏',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            116 =>
            array(
                'id' => '117',
                'country' => 'South African Rand',
                'currency_code' => 'ZAR',
                'currency_symbol' => 'R',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            117 =>
            array(
                'id' => '118',
                'country' => 'Zambian Kwacha',
                'currency_code' => 'ZMK',
                'currency_symbol' => 'ZK',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            118 =>
            array(
                'id' => '119',
                'country' => 'Zimbabwean Dollar',
                'currency_code' => 'ZWL',
                'currency_symbol' => 'Z$',
                'exchange_rate' => '1.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        );
        foreach($data as $item){
            unset($item['id']);
            unset($item['created_at']);
            unset($item['updated_at']);
            Currency::create($item);
        }
    }
}
