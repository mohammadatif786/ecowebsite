<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMarketplaceEmail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    public $user;
    public $topPicks;
    public $popularSellers;
    protected string $appUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $topPicks = null, $popularSellers = null)
    {
        $this->user = $user;
        $this->topPicks = $topPicks;
        $this->popularSellers = $popularSellers;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Discover LinkUp Marketplace',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ad = $this->getSponsorAd('marketplace', $this->user->country_code);

        return new Content(
            view: 'emails.market_place_email',
            with: [
                'user' => $this->user,
                'appUrl' => $this->appUrl,
                'logoUrl' => $this->appUrl . 'assets/images/logo.png',
                'topPicks' => $this->topPicks,
                'popularSellers' => $this->popularSellers,
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
