<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailSponsorAd;

class EventMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;


    public $user;
    public $events;
    public $cookouts;
    public $wellness;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $events, $cookouts = null, $wellness = null)
    {
        $this->user = $user;
        $this->events = $events;
        $this->cookouts = $cookouts;
        $this->wellness = $wellness;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event Mail',
        );
    }

    /**
     * Get the message content d
     * efinition.
     */
    public function content(): Content
    {
        $ad = $this->getSponsorAd('events', $this->user->country_code);

        return new Content(
            view: 'emails.eventEmail',
            with: [
                'user' => $this->user,
                'events' => $this->events,
                'cookouts' => $this->cookouts,
                'wellness' => $this->wellness,
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
