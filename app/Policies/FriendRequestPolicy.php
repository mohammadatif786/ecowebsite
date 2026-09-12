<?php

namespace App\Policies;

use App\Models\Frontend\FriendRequest;
use App\Models\User;

class FriendRequestPolicy
{
    public function respond(User $user, FriendRequest $request): bool
    {
        return (int) $request->receiver_id === (int) $user->id && (int) $request->status === 0;
    }
}
