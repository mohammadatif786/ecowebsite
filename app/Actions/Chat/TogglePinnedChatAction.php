<?php

namespace App\Actions\Chat;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class TogglePinnedChatAction
{
    public function toggleUnPinnedUser(User $user, int $userId)
    {
        try {
            $chatUser = $user->friends()->where('id', $userId)->first();

            if (! $chatUser) {
                return response()->json(['success' => 'User not found in friends list.'], 404);
            }

            $updatePinned = User::findOrFail($userId);
            $updatePinned->pinned = $updatePinned->pinned === 1 ? 0 : 1;
            $updatePinned->save();

            return $updatePinned;
        } catch (\Throwable $th) {
            Log::error('find issue when toggle un pinned user', [
                'error' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
        }
    }

    public function togglePinUser(User $user, int $userId)
    {
        try {
            $chatUser = $user->friends()->where('id', $userId)->first();

            if (! $chatUser) {
                return response()->json(['success' => 'User not found in friends list.'], 404);
            }

            $updatePinned = User::findOrFail($userId);
            $updatePinned->pinned = $updatePinned->pinned === 1 ? 0 : 1;
            $updatePinned->save();

            return $updatePinned;
        } catch (\Throwable $th) {
            Log::error('find issue when toggle pinned user', [
                'error' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
        }
    }
}
