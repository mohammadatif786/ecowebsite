<?php

namespace App\Notifications\User\Signup;

use App\Mail\Signup\PasswordResetLinkMail;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Sends the LinkUp-styled reset email instead of Laravel's default one.
 * Used by User::sendPasswordResetNotification().
 */
class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): PasswordResetLinkMail
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new PasswordResetLinkMail($notifiable, $url, (int) config('auth.passwords.users.expire')))
            ->to($notifiable->getEmailForPasswordReset());
    }
}
