<?php

namespace App\Domain\Linkup\Actions;

use App\Domain\Linkup\DTOs\SwipeProfileData;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class SwipeProfileAction
{
    public function execute(User $actor, User $target, SwipeProfileData $data): array
    {
        return DB::transaction(function () use ($actor, $target, $data) {
            UserMatch::updateOrCreate(
                ['user_id' => $actor->id, 'target_user_id' => $target->id],
                ['status' => $data->action],
            );

            $match = $data->action === 'like' && UserMatch::query()
                ->where('user_id', $target->id)
                ->where('target_user_id', $actor->id)
                ->where('status', 'like')
                ->exists();

            if ($match) {
                DB::afterCommit(fn () => Notification::firstOrCreate([
                    'user_id' => $target->id, 'send_by' => (string) $actor->id, 'type' => 'mutual_match',
                ], ['title' => 'New Match', 'message' => 'You have a new mutual match with '.$actor->name.'.', 'unread' => true]));
            }
            return ['success' => true, 'match' => $match];
        }, 3);
    }
}
