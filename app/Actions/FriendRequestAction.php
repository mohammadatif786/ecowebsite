<?php

namespace App\Actions;

use App\Models\Frontend\FriendRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FriendRequestAction
{
    public function sendRequest(string $slug, string $user)
    {
        $user = User::where('uid', $user)->first();

        $userLoggedin = Auth::user();
        $friendRequest = FriendRequest::where('user_id', $userLoggedin->id)
            ->where('receiver_id', $user->id)
            ->first();

        if ($friendRequest) {
            return back()->with('message', 'Friend request already sent!');
        }

        FriendRequest::create([
            'user_id' => $userLoggedin->id,
            'receiver_id' => $user->id,
            'status' => 0,
            'type' => 0,
        ]);

        Notification::create([
            'title'    => 'New Friend Request',
            'message'  => "{$userLoggedin->name} sent you a friend request.",
            'send_by'  => $userLoggedin->id,
            'user_id'  => $user->id,
            'type'     => 'friend_request',
            'unread'   => true,
            'avatar'   => $userLoggedin->avatar ?? null,
            'metadata' => [
                'sender_id'   => $userLoggedin->id,
                'sender_name' => $userLoggedin->name,
            ],
        ]);
    }
}
