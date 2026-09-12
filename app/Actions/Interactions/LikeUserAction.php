<?php

namespace App\Actions\Interactions;

use App\Jobs\LikeUserEmailJob;
use App\Jobs\SendMatchEmailJob;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\PopularityScoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LikeUserAction
{

    /**
     * Handle the action of liking a user. This method will create or update a UserMatch record with 'like' status, bump the target user's popularity score, and check for a mutual like to determine if it's a match. It also sends appropriate notifications and emails based on the interaction outcome.
     * @param User $targetUser The user being liked.
     * @return bool Returns true if the operation was successful.
     * @throws \Exception Throws an exception if any database operation fails.
     */
    public function execute(User $targetUser)
    {

        $authUser = Auth::user();

        return DB::transaction(function () use ($authUser, $targetUser) {

            UserMatch::updateOrCreate(
                ['user_id'        => $authUser->id, 'target_user_id' => $targetUser->id],
                ['status' => 'like']
            );

            app(PopularityScoreService::class)->bump($targetUser, 3);

            $isMatch = UserMatch::where('user_id', $targetUser->id)
                ->where('target_user_id', $authUser->id)
                ->where('status', 'like')
                ->exists();

            if ($isMatch) {
                $this->initialBothLikeMail($authUser, $targetUser);
            } else {
                $this->initialLikeMail($authUser, $targetUser);
            }

            return true;
        });
    }

    /**
     * Send a notification and email to the target user when they receive a like from the authenticated user. This method creates a 'like' type notification and sends an email using the LikeUserEmailMail Mailable class.
     * @param User $authUser The user who performed the like action.
     * @param User $targetUser The user who received the like.
     */
    private function initialLikeMail(User $authUser, User $targetUser)
    {
        $this->createNotification($authUser, $targetUser, 'like');
        LikeUserEmailJob::dispatch($targetUser, $authUser)->delay(now()->addMinutes(5));
    }

    /**
     * Handle the scenario when both users have liked each other, indicating a match. This method creates 'match' type notifications for both users and dispatches a job to send a match email to the authenticated user.
     * @param User $authUser The user who performed the like action.
     * @param User $targetUser The user who also liked the authenticated user, resulting in a match.
     */
    private function initialBothLikeMail(User $authUser, User $targetUser)
    {
        $this->createNotification($authUser, $targetUser, 'match');
        $this->createNotification($targetUser, $authUser, 'match');

        SendMatchEmailJob::dispatch($authUser, $targetUser)->delay(now()->addMinutes(7));
    }

    /**
     * Create a notification for the target user based on the type of interaction (like or match). This method constructs the notification title and message according to the interaction type and saves it to the database.
     * @param User $sender The user who initiated the interaction (like or match).
     * @param User $receiver The user who receives the notification about the interaction.
     * @param string $type The type of interaction, either 'like' or 'match'. This determines the content of the notification.
     * @return Notification The created notification instance.
     */
    private function createNotification(User $sender, User $receiver, string $type)
    {

        $title = $type === 'match' ? "It's a Match!" : "New Like!";
        $message = $type === 'match'
            ? "You and {$sender->name} liked each other! Start chatting."
            : "{$sender->name} liked your profile.";

        return Notification::create([
            'title'   => $title,
            'message' => $message,
            'send_by' => $sender->id,
            'user_id' => $receiver->id,
            'type'    => $type,
            'context' => 'match_context',
            'unread'  => true,
            'avatar'  => $sender->avatar ?? null,
        ]);
    }

    public function disLike(User $user)
    {
        $userLoggedin = Auth::user();

        UserMatch::updateOrCreate(
            [
                'user_id'        => $userLoggedin->id,
                'target_user_id' => $user->id,
            ],
            [
                'status' => 'dislike',
            ]
        );

        Notification::create([
            'title' => "{$userLoggedin->name} disliked you!",
            'message' => "{$userLoggedin->name} disliked your peofile ",
            'send_by' => $userLoggedin->id,
            'user_id' => $user->id,
            'type'    => 'match',
            'context' => 'match_context',
            'unread'  => true,
            'avatar'  => $userLoggedin->avatar ?? null,
        ]);
    }
}
