<?php

namespace App\Services;

use App\Actions\Chat\GetChatUsersAction;
use App\Actions\Chat\GetConversationAction;
use App\Actions\Chat\TogglePinnedChatAction;
use App\Contracts\ChatServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChatService implements ChatServiceInterface
{
    private $getChatUsersAction;
    private $togglePinnedChatAction;
    private $getConversationAction;

    public function __construct(GetChatUsersAction $chatAction, TogglePinnedChatAction $toggleUnPin, GetConversationAction $getconversation)
    {
        $this->getChatUsersAction = $chatAction;
        $this->togglePinnedChatAction = $toggleUnPin;
        $this->getConversationAction = $getconversation;
    }
    public function getChatUsers()
    {
        $user = Auth::user();

        $friends = $user->friends()->get();

        $chatUsers = $this->getChatUsersAction->execute($friends, $user);

        return $chatUsers;
    }

    public function toggleUnPin(int $userId)
    {
        $user = Auth::user();

        $toggleUnPin = $this->togglePinnedChatAction->toggleUnPinnedUser($user, $userId);

        return $toggleUnPin;
    }

    public function togglePin(int $userId)
    {
        $user = Auth::user();

        $togglePin = $this->togglePinnedChatAction->togglePinUser($user, $userId);

        return $togglePin;
    }

    public function getConversation(string $slug, string $user, Request $request)
    {
        $receiver = User::where('uid', $user)->first();
        $authUser = Auth::user();
        $isFriend = $authUser->friends()->where('id', $receiver->id)->exists();
        $isMutualLike = $authUser->isMatchedWith($receiver);

        $conversation = $this->getConversationAction->getConversaction($receiver, $isFriend, $isMutualLike, $request, $user);

        return $conversation;
    }
}
