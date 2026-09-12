<?php

namespace App\Actions\Chat;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class GetChatUsersAction
{

    public function execute(Collection $friends, User $user)
    {
        $mutualMatches = $this->mutualMatches($user);

        $chatUsers = $this->chatUsers($friends, $mutualMatches, $user);

        return $chatUsers;
    }

    private function mutualMatches(User $user)
    {
        try {
            $mutualMatches = User::select(
                'id',
                'uid',
                'name',
                'avatar',
                'pinned'
            )
                ->where('id', '!=', $user->id)
                ->whereHas('matchesSent', function ($q) use ($user) {
                    $q->where('status', 'like')
                        ->where('target_user_id', $user->id);
                })
                ->whereHas('matchesReceived', function ($q) use ($user) {
                    $q->where('status', 'like')
                        ->where('user_id', $user->id);
                })
                ->get();


            return $mutualMatches;
        } catch (\Throwable $th) {
            Log::error('mutual matches user not found something their is issue!', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
        }
    }

    private function chatUsers(Collection $friends, Collection $mutualMatches, User $user)
    {
        $chatUsers = $friends->merge($mutualMatches)
            ->unique('id')
            ->map(function ($chatUser) use ($user) {

                $lastMessage = Message::query()
                    ->where(function ($q) use ($user, $chatUser) {
                        $q->where('from_user_id', $user->id)
                            ->where('to_user_id', $chatUser->id);
                    })
                    ->orWhere(function ($q) use ($user, $chatUser) {
                        $q->where('from_user_id', $chatUser->id)
                            ->where('to_user_id', $user->id);
                    })
                    ->latest()
                    ->first([
                        'id',
                        'from_user_id',
                        'to_user_id',
                        'content',
                        'type',
                        'created_at',
                        'is_read',
                    ]);

                return [
                    'id' => $chatUser->id,
                    'uid' => $chatUser->uid,
                    'name' => $chatUser->name,
                    'avatar' => $chatUser->avatar,
                    'pinned' => $chatUser->pinned,

                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'type' => $lastMessage->type,
                        'from_user_id' => $lastMessage->from_user_id,
                        'to_user_id' => $lastMessage->to_user_id,
                        'is_read' => $lastMessage->is_read,
                        'created_at' => $lastMessage->created_at,
                    ] : null,
                ];
            })
            ->sortBy('name')
            ->values();

        return $chatUsers;
    }
}
