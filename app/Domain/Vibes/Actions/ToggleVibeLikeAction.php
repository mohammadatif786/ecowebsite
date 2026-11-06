<?php

namespace App\Domain\Vibes\Actions;

use App\Models\User;
use App\Models\Vibe;
use Illuminate\Support\Facades\DB;

/**
 * Like / unlike a post. The post row is locked while the like is added or removed, and the count is
 * set from the real number of likes, so fast double taps can't add two likes or push the count off.
 */
class ToggleVibeLikeAction
{
    public function execute(User $user, Vibe $vibe): array
    {
        return DB::transaction(function () use ($user, $vibe) {
            Vibe::query()->whereKey($vibe->getKey())->lockForUpdate()->first();

            $likes = $vibe->likes()->where('user_id', $user->id);
            $isLiked = ! $likes->exists();

            if ($isLiked) {
                $vibe->likes()->create(['user_id' => $user->id, 'type' => 'like']);
            } else {
                $vibe->likes()->where('user_id', $user->id)->delete();
            }

            $count = $vibe->likes()->count();
            Vibe::query()->whereKey($vibe->getKey())->update(['likes_count' => $count]);

            return ['is_liked' => $isLiked, 'likes_count' => $count];
        });
    }
}
