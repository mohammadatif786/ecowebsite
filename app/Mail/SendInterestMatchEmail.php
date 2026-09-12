<?php

namespace App\Mail;

use App\Models\EmailSponsorAd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendInterestMatchEmail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    public $user;
    public $userMatches;
    protected string $appUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $userMatches)
    {
        $this->user = $user;
        $this->userMatches = $userMatches;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Top Interest Matches on LinkUp!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ad = $this->getSponsorAd('like_received', $this->user->country_code);


        return new Content(
            view: 'emails.interest_match',
            with: [
                'user' => $this->user,
                'userMatches' => $this->userMatches,
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
