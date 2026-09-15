<?php

namespace App\Domain\Vibes\Actions;

use App\Models\User;
use App\Models\Vibe;
use App\Models\VibeComment;

class PostVibeCommentAction
{
    public function execute(User $user, Vibe $vibe, string $commentText): VibeComment
    {
        return $vibe->comments()->create([
            'user_id' => $user->id,
            'comment' => $commentText,
        ]);
    }
}
