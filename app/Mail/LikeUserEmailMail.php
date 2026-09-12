<?php

namespace App\Mail;

use App\Models\EmailSponsorAd;
use App\Models\User;
use App\Traits\InjectSponsorAd;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LikeUserEmailMail extends Mailable
{
    use Queueable, SerializesModels, InjectSponsorAd;

    /**
     * Create a new message instance.
     */
    protected User $likedUser;
    protected User $liker;
    protected string $appUrl;

    public function __construct(User $likedUser, User $liker)
    {
        $this->likedUser = $likedUser;
        $this->liker = $liker;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have a new like on your profile! 🎉',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ad = EmailSponsorAd::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->inRandomOrder()
            ->first();

        return new Content(
            view: 'emails.LikeUserEmail',
            with: [
                'likedUser' => $this->likedUser,
                'liker' => $this->liker,
                'appUrl' => $this->appUrl,
                'logoUrl' => $this->appUrl . 'assets/images/logo.png',
                'ad' => $ad,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
