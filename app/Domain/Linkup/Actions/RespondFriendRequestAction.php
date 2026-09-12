<?php

namespace App\Domain\Linkup\Actions;

use App\Models\Frontend\FriendRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RespondFriendRequestAction
{
    public function execute(User $receiver, FriendRequest $request, bool $accept): void
    {
        DB::transaction(function () use ($receiver, $request, $accept) {
            if (! $accept) { $request->delete(); return; }
            $request->update(['status' => 1]);
            DB::afterCommit(function () use ($receiver, $request) {
                Notification::firstOrCreate([
                    'user_id' => $request->user_id, 'send_by' => (string) $receiver->id, 'type' => 'friend_request_accepted',
                ], ['title' => 'Friend Request Accepted', 'message' => $receiver->name.' accepted your friend request.', 'unread' => true]);
            });
        }, 3);
    }
}
