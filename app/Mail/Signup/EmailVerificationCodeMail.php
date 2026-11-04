<?php

namespace App\Mail\Signup;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The 6-digit code a new member types on the Verify email page.
 */
class EmailVerificationCodeMail extends Mailable
{
    public function __construct(
        public User $user,
        public string $code,
        public int $ttlMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->code.' is your LinkUp code');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.signup.verification-code');
    }
}
