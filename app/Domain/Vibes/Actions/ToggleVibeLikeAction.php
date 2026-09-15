<?php

namespace App\Domain\Vibes\Actions;

use App\Models\User;
use App\Models\Vibe;

class ToggleVibeLikeAction
{
    public function execute(User $user, Vibe $vibe): array
    {
        $like = $vibe->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $vibe->decrement('likes_count');
            $isLiked = false;
        } else {
            $vibe->likes()->create([
                'user_id' => $user->id,
                'type' => 'like',
            ]);
            $vibe->increment('likes_count');
            $isLiked = true;
        }

        return [
            'is_liked' => $isLiked,
            'likes_count' => $vibe->likes_count,
        ];
    }
}
