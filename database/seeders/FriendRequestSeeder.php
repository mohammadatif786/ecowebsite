<?php

namespace Database\Seeders;

use App\Models\Frontend\FriendRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FriendRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            0 =>
            array(
                'id' => 'f9d8d3ffb37b4301a045',
                'user_id' => 'bxjTLg891aUjL9jiQn8JiqnczlI2',
                'receiver_id' => '6ySALchvEGMSkJ5nvjEKUW56Fpy2',
                'type' => 0,
                'timestamp' => 1713957687,
                'status' => 1,
            ),
        );

        DB::table('friend_requests')->truncate();
        foreach ($data as $item) {
            if (array_key_exists('user_id', $item) && array_key_exists('receiver_id', $item)) {
                $user = User::where('uid', $item['user_id'])->first();
                $reciever_user = User::where('uid', $item['receiver_id'])->first();
                if ($user && $reciever_user) {
                    FriendRequest::create([
                        'receiver_id' => $reciever_user->id,
                        'user_id' => $user->id,
                        'status' => $item['status'],
                        'type' => $item['status'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
