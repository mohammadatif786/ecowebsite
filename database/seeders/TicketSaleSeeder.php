<?php

namespace Database\Seeders;

use App\Models\LinkUpEvent;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '0Pi7WTopwbcaK67S0pN2',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022058300063,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '0Pi7WTopwbcaK67S0pN2',
                'ticket_status' => 'canceled',
            ),
            1 =>
            array(
                'firebase_id' => '14zxMakmV75gmj81Piin',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027261548264,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'fd7917e729d44946bf75',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '14zxMakmV75gmj81Piin',
                'ticket_status' => 'canceled',
            ),
            2 =>
            array(
                'firebase_id' => '1dxUJWKsIYnYTvSmelrA',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697107770,
                'created' => 1697107846958775,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '1dxUJWKsIYnYTvSmelrA',
            ),
            3 =>
            array(
                'firebase_id' => '2gWEEBpkukocN669xLZE',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697021937,
                'created' => 1697021985896666,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'demo',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '2gWEEBpkukocN669xLZE',
            ),
            4 =>
            array(
                'firebase_id' => '3fngUiiB0Udii9Rz94pb',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027234369919,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'fd7917e729d44946bf75',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '3fngUiiB0Udii9Rz94pb',
                'ticket_status' => 'canceled',
            ),
            5 =>
            array(
                'firebase_id' => '5lO7UvRHanJEgODvxJNc',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027094185090,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'fd7917e729d44946bf75',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '5lO7UvRHanJEgODvxJNc',
            ),
            6 =>
            array(
                'firebase_id' => '7E0x3AVJxmCXzYNaWJUD',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1717496198,
                'created' => 1717496454540307,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '2a81c46113ce4cc2b19c',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '4c89ba9652cb4c87999c',
                'pay_type' => 'Wallet',
                'ticket_name' => 'free',
                'user_id' => 'R8mbSfBVBNZWsogWSFJg3WV93eL2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => '7E0x3AVJxmCXzYNaWJUD',
            ),
            7 =>
            array(
                'firebase_id' => 'Am8AiINcqu9PSnqWZEav',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697107770,
                'created' => 1697109610324900,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'Am8AiINcqu9PSnqWZEav',
            ),
            8 =>
            array(
                'firebase_id' => 'DKh1S8i7MTC6JJCbxMQ0',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697172475,
                'created' => 1697172561179083,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'DKh1S8i7MTC6JJCbxMQ0',
            ),
            9 =>
            array(
                'firebase_id' => 'EyTJA7izTOcPC8472RPX',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1698984088,
                'created' => 1700466016226355,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => '0nEtTI1nZZWrxBItbshWY0KkneS2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'EyTJA7izTOcPC8472RPX',
            ),
            10 =>
            array(
                'firebase_id' => 'FKjiQk6KNj0alcTb8x5D',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022058304866,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'FKjiQk6KNj0alcTb8x5D',
            ),
            11 =>
            array(
                'firebase_id' => 'ITefaqButygBSBxRVhEz',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1700465964,
                'created' => 1700639335101903,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => '0nEtTI1nZZWrxBItbshWY0KkneS2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'ITefaqButygBSBxRVhEz',
                'ticket_status' => 'canceled',
            ),
            12 =>
            array(
                'firebase_id' => 'IUOl09ldNbMkwnOmcd4W',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1708013637,
                'created' => 1708013692699856,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '8da34b25242045589f37',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '2d0ba142f2714a918a30',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free',
                'user_id' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'IUOl09ldNbMkwnOmcd4W',
            ),
            13 =>
            array(
                'firebase_id' => 'IYI9LQhc8rXTcqtodofn',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697107770,
                'created' => 1697109610328622,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'IYI9LQhc8rXTcqtodofn',
            ),
            14 =>
            array(
                'firebase_id' => 'Ibxz62JRgdOV6V3WtuEU',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027261547661,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'fd7917e729d44946bf75',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'Ibxz62JRgdOV6V3WtuEU',
            ),
            15 =>
            array(
                'firebase_id' => 'KGmgDWoy6I0R5pJhQ4sC',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022058309681,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'KGmgDWoy6I0R5pJhQ4sC',
            ),
            16 =>
            array(
                'firebase_id' => 'L2qFf9YC6iNRA6lHXdzN',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021937,
                'created' => 1697021985899664,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'L2qFf9YC6iNRA6lHXdzN',
            ),
            17 =>
            array(
                'firebase_id' => 'N5vni5cXwtdsynsXcS5R',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1708013637,
                'created' => 1708013702408806,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '8da34b25242045589f37',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '2d0ba142f2714a918a30',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free',
                'user_id' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'N5vni5cXwtdsynsXcS5R',
            ),
            18 =>
            array(
                'firebase_id' => 'RFK60Hb9nxsA6d4E9t2s',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1709159574,
                'created' => 1709160871934410,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '48b360aaeeb04ede9ca2',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '1c6844fa556b4df7ba67',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free For all',
                'user_id' => 'bESfJw87RGecuTmqVudDHkMTx4l2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'RFK60Hb9nxsA6d4E9t2s',
            ),
            19 =>
            array(
                'firebase_id' => 'T3bmrVzhHmUaHyegkXWB',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1714994929,
                'created' => 1714995068946237,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1ef04f400279452696f7',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '4c89ba9652cb4c87999c',
                'pay_type' => 'Wallet',
                'ticket_name' => 'free buy',
                'user_id' => 'lFBRNPnkJWSuG9qhCCfgpeY5SB22',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'T3bmrVzhHmUaHyegkXWB',
                'ticket_status' => 'Confirmed',
            ),
            20 =>
            array(
                'firebase_id' => 'TCb9Q0ER3q5Zio0PDGQU',
                'ticket_type' => 'paid',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1717496433,
                'created' => 1717496771516351,
                'fee' => 9.25,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 30.59,
                'coupan_amount' => '0.00',
                'ticket_id' => 'c9460a0f351c458bb451',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'paid',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 139.84,
                'event_id' => '4c89ba9652cb4c87999c',
                'pay_type' => 'Online',
                'ticket_name' => 'Paid',
                'user_id' => 'gKoUu3TTHtNIFLTar9j8R3Gkuiw2',
                'sub_total' => 139.84,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'TCb9Q0ER3q5Zio0PDGQU',
            ),
            21 =>
            array(
                'firebase_id' => 'TN1MTC5kshwotutNAV17',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1715056198,
                'created' => 1715056428707001,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => 'lFBRNPnkJWSuG9qhCCfgpeY5SB22',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'TN1MTC5kshwotutNAV17',
            ),
            22 =>
            array(
                'firebase_id' => 'X2csMM3ByXxbdknX2sdN',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027246417007,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'fd7917e729d44946bf75',
                'pay_type' => 'Wallet',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'X2csMM3ByXxbdknX2sdN',
            ),
            23 =>
            array(
                'firebase_id' => 'ZkEytH16c8E4b0rN3f2v',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1709159574,
                'created' => 1709161472028908,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '48b360aaeeb04ede9ca2',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '1c6844fa556b4df7ba67',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free For all',
                'user_id' => 'bESfJw87RGecuTmqVudDHkMTx4l2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'ZkEytH16c8E4b0rN3f2v',
            ),
            24 =>
            array(
                'firebase_id' => 'aHC2sDyhLOzXbbYEmATB',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027094185801,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'fd7917e729d44946bf75',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'aHC2sDyhLOzXbbYEmATB',
            ),
            25 =>
            array(
                'firebase_id' => 'bRyNA7CI0nTC3gGdmDJ8',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021937,
                'created' => 1697021985902741,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'bRyNA7CI0nTC3gGdmDJ8',
            ),
            26 =>
            array(
                'firebase_id' => 'eyMx0VdYDp2LKcRt3j6y',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1699409787,
                'created' => 1699409962116770,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '48b360aaeeb04ede9ca2',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '1c6844fa556b4df7ba67',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free For all',
                'user_id' => '79v8MSKAChbchalPHAI15Q7fFGm2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'eyMx0VdYDp2LKcRt3j6y',
            ),
            27 =>
            array(
                'firebase_id' => 'h4pnCJkUuWkMiI1vo6f4',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1698984088,
                'created' => 1700466016216076,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => '0nEtTI1nZZWrxBItbshWY0KkneS2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'h4pnCJkUuWkMiI1vo6f4',
                'ticket_status' => 'canceled',
            ),
            28 =>
            array(
                'firebase_id' => 'h9Q5xsx9490BaaQ3urjJ',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022027098153,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'h9Q5xsx9490BaaQ3urjJ',
            ),
            29 =>
            array(
                'firebase_id' => 'iRJcEthbIDJ7phoWM7nq',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027211216309,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'fd7917e729d44946bf75',
                'pay_type' => 'Wallet',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'iRJcEthbIDJ7phoWM7nq',
            ),
            30 =>
            array(
                'firebase_id' => 'kc43cq8IBLTfbh45DZlr',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1702312138,
                'created' => 1703501183987151,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1145d6fdd7ec4dbb880c',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '2d0ba142f2714a918a30',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free For all',
                'user_id' => 'AkPTQfWkcAXzjETqMs2qhMITUCs2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'kc43cq8IBLTfbh45DZlr',
            ),
            31 =>
            array(
                'firebase_id' => 'lVlGrSPTo4KOCBY6YVzO',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022027108704,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'lVlGrSPTo4KOCBY6YVzO',
            ),
            32 =>
            array(
                'firebase_id' => 'ldLEyNrwWJ7PIJXnqJR8',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1717496198,
                'created' => 1717496480338412,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '2a81c46113ce4cc2b19c',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '4c89ba9652cb4c87999c',
                'pay_type' => 'Wallet',
                'ticket_name' => 'free',
                'user_id' => 'gKoUu3TTHtNIFLTar9j8R3Gkuiw2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'ldLEyNrwWJ7PIJXnqJR8',
            ),
            33 =>
            array(
                'firebase_id' => 'p4a3yIdnMxIUbFnGoyo5',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027246416057,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'fd7917e729d44946bf75',
                'pay_type' => 'Wallet',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'p4a3yIdnMxIUbFnGoyo5',
            ),
            34 =>
            array(
                'firebase_id' => 'qCumvLjX2WBObK5Zpp07',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1708013637,
                'created' => 1708013702409872,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '8da34b25242045589f37',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '2d0ba142f2714a918a30',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free',
                'user_id' => 'JaXt7eTzkSOhTqYIC27EeW5oh1m1',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'qCumvLjX2WBObK5Zpp07',
            ),
            35 =>
            array(
                'firebase_id' => 'qQIfRq5pB8eUrkFjIFWX',
                'ticket_status' => 'Confirmed',
                'ticket_type' => 'free',
                'ticket_qrcode' => 1697021937,
                'created' => 1697021985892466,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'qQIfRq5pB8eUrkFjIFWX',
            ),
            36 =>
            array(
                'firebase_id' => 'r2FR56KeNYMiN5ac3Uyu',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027234369350,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'fd7917e729d44946bf75',
                'pay_type' => 'Wallet',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'r2FR56KeNYMiN5ac3Uyu',
            ),
            37 =>
            array(
                'firebase_id' => 'sm0DjYBLJfFb1hVp9peb',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697021937,
                'created' => 1697022058290375,
                'fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'no_of_tickets' => 1,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'pay_type' => 'Wallet',
                'event_id' => 'e4eb55c6d74944bfa073',
                'ticket_name' => 'demo',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'sm0DjYBLJfFb1hVp9peb',
            ),
            38 =>
            array(
                'firebase_id' => 'uIqlHnI24FmdnO631Pc3',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697027061,
                'created' => 1697027211217169,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '1d5c9a77adfe4d919816',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'fd7917e729d44946bf75',
                'pay_type' => 'Wallet',
                'ticket_name' => 'General Ticket',
                'user_id' => 'ZUIzSWlVdDNpLlzrtLSqtuIsmk72',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'uIqlHnI24FmdnO631Pc3',
            ),
            39 =>
            array(
                'firebase_id' => 'vk3kIxVAHYqjsotzdbgT',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697021899,
                'created' => 1697022027103659,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'vk3kIxVAHYqjsotzdbgT',
            ),
            40 =>
            array(
                'firebase_id' => 'xQ3GLRCBZcV6WDAdv29g',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1715056198,
                'created' => 1715056450769493,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '5cf58aefddd8430eb574',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'demo',
                'user_id' => 'lFBRNPnkJWSuG9qhCCfgpeY5SB22',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'xQ3GLRCBZcV6WDAdv29g',
            ),
            41 =>
            array(
                'firebase_id' => 'yHT7U9fg1lKkNourSb0k',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1697107770,
                'created' => 1697109259837772,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => 'ab48b5dfdafb4b7b9b6b',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => 'e4eb55c6d74944bfa073',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Test',
                'user_id' => 'aTbLpbtyV5UuwbWU7iPuZRcf8lc2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'yHT7U9fg1lKkNourSb0k',
            ),
            42 =>
            array(
                'firebase_id' => 'zZWraPbvSf1o96jLGyhi',
                'ticket_type' => 'free',
                'ticket_status' => 'Confirmed',
                'ticket_qrcode' => 1699409787,
                'created' => 1699409962115985,
                'fee' => 0,
                'discount' => 0,
                'no_of_tickets' => 1,
                'tax' => 0,
                'coupan_amount' => '0.00',
                'ticket_id' => '48b360aaeeb04ede9ca2',
                'seat_details' =>
                array(
                    0 =>
                    array(
                        'ticket_type' => 'free',
                        'no_of_tickets' => 1,
                    ),
                ),
                'total' => 0,
                'event_id' => '1c6844fa556b4df7ba67',
                'pay_type' => 'Wallet',
                'ticket_name' => 'Free For all',
                'user_id' => '79v8MSKAChbchalPHAI15Q7fFGm2',
                'sub_total' => 0,
                'payment_method' => 'cash',
                'ticket_qrcode_id' => 'zZWraPbvSf1o96jLGyhi',
            ),
        );


        foreach ($data as $item) {
            $item['link_up_event_id'] = LinkUpEvent::where('id', $item['event_id'])->first()?->id ?? null;
            $item['user_id'] = User::where('uid', $item['user_id'])->first()?->id ?? null;
            $item['ticket_id'] = Ticket::where('id', $item['ticket_id'])->first()?->id ?? null;
            unset($item['seat_details']);
            unset($item['event_id']);
            unset($item['created']);
            TicketSale::updateOrCreate(
                ['ticket_id' => $item['ticket_id']],
                $item
            );
        }
    }
}
