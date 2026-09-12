<?php

namespace Database\Seeders;

use App\Models\OrgSignUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrgSignUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'firebase_id' => '3ee463e6855a43fd95ab',
                'first_name' => 'Shaholin',
                'last_name' => 'Rolle',
                'user_id' => 'PS9xyC1w9GcCsPoJRYNIOOKGoL22',
                'website' => NULL,
                'address' => 'Glaexy Lounge Grounds',
                'role' => 'Admin',
                'passport' => NULL,
                'driver_license' => NULL,
                'telephone' => '242-727-9178',
                'email' => 'Shaholin@gmail.com',
                'radio' => NULL,
                'address_proof' => 'https://firebasestorage.googleapis.com/v0/b/linkup-cfa21.appspot.com/o/users%2F18NBLnkYXYRSnej1v1oEokJWJHH3%2FMicheal1683556213599(0).jpg?alt=media&token=df4ecfe2-c22f-4ceb-bfb5-63cd2eb2ad99',
                'back_side' => 'https://firebasestorage.googleapis.com/v0/b/linkup-cfa21.appspot.com/o/users%2F18NBLnkYXYRSnej1v1oEokJWJHH3%2FMicheal1683556213599(0).jpg?alt=media&token=df4ecfe2-c22f-4ceb-bfb5-63cd2eb2ad99',
                'front_side' => 'https://firebasestorage.googleapis.com/v0/b/linkup-cfa21.appspot.com/o/users%2F18NBLnkYXYRSnej1v1oEokJWJHH3%2FMicheal1683556213599(0).jpg?alt=media&token=df4ecfe2-c22f-4ceb-bfb5-63cd2eb2ad99',
                'kyc_status' => 'approved',
            ),
            1 =>
            array(
                'firebase_id' => '4e75a84349874c559105',
                'first_name' => 'Anoop kumar',
                'last_name' => 'kumar',
                'user_id' => 'Lcp0Sn7brCYCImrCHKEYmc2R5C32',
                'website' => 'adsadasdasd',
                'address' => 'qqqqqqqqq',
                'role' => 'developer',
                'passport' => 'eventOrganizers/_1709720622.jpg',
                'driver_license' => NULL,
                'telephone' => '6280572771',
                'email' => 'anoopjaryal987@gmail.com',
                'radio' => 'q',
            ),
            2 =>
            array(
                'firebase_id' => '5ddwRq1FZ7yjmvnhuq9V',
                'user_id' => 'htIjWtFqcmd7ma3ztgBMuXl6lc32',
                'first_name' => 'Cassius ',
                'last_name' => 'Stuart ',
                'website' => 'www.test.com ',
                'address' => ' Bay street ',
                'role' => '',
                'telephone' => '24224422442',
                'email' => 'cstuart12@caribmedltd.com',
                'radio' => '',
            ),
            3 =>
            array(
                'firebase_id' => 'lXGXG6M9ERutVf1KhC4U',
                'user_id' => 'MzurIVq8ORM1IQoZ7T8RFp9Tcbn1',
                'first_name' => 'Hamail ',
                'last_name' => 'Shahid ',
                'website' => 'guugu',
                'address' => 'Hydy',
                'role' => '',
                'telephone' => '03155753409',
                'email' => 'hamailshahid6@gmail.com',
                'radio' => '',
            ),
            4 =>
            array(
                'firebase_id' => 'oB03pcsbeACQKewhkQVz',
                'user_id' => '8aB58BvpfMXk9UiwQRcKIL6kPOj1',
                'first_name' => 'avain',
                'last_name' => 'akka',
                'website' => 'www.gmail.com',
                'address' => 'anajaj',
                'role' => '',
                'telephone' => '45464664554646',
                'email' => 'a@gmail.com',
                'radio' => '',
            ),
            5 =>
            array(
                'firebase_id' => 'pSyLG2Xg6XxIPIfi1XoU',
                'user_id' => 'u96P33AIiacBuFBPIBtqODWxXul1',
                'first_name' => 'Abdullah',
                'last_name' => 'Nazeer',
                'website' => 'akdkafs',
                'address' => 'buinss man colony',
                'role' => '',
                'telephone' => '83727847981483',
                'email' => 'abdullah@gmail.com',
                'radio' => '',
                'kyc_status' => 'pending',
            ),
        );

        foreach ($data as $item) {
            $user = User::where('uid', $item['user_id'])->first();
            $item['user_id'] = $user?->id ?? null;
            $item['created_at'] = Carbon::now();
            $item['updated_at'] = Carbon::now();
//             OrgSignUser::create($item);
        }
    }
}
