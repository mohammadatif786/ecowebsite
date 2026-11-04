<?php

namespace App\Mail\Signup;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The "Reset your password" email, in the same style as the sign-up code email.
 */
class PasswordResetLinkMail extends Mailable
{
    public function __construct(
        public User $user,
        public string $url,
        public int $expireMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset your LinkUp password');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.signup.password-reset');
    }
}
