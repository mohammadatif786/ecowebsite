<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    /**
     * Create a new message instance.
     */
    public $event;
    public $eventLink;

    public function __construct($event, $eventLink)
    {
        $this->event = $event;
        $this->eventLink = $eventLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You’re Invited! 🎉 ' . $this->event['title'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ad = $this->getSponsorAd('events', $this->event->country_code);


        return new Content(
            view: 'emails.EventInvitationMail',
            with: [
                'event' => $this->event,
                'eventLink' => $this->eventLink,
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
