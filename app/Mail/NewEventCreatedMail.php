<?php

namespace App\Mail;

use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEventCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LinkUpEvent $event,
        public OrganizerProfile $organizer,
        public User $recipient,
        public ?string $trackingToken = null,
    ) {}

    public function envelope(): Envelope
    {
        $organizerName = $this->organizer->organizer_name
            ?? $this->organizer->user?->name
            ?? $this->event->organizer_name
            ?? 'Organizer';

        return new Envelope(
            subject: 'New event from ' . $organizerName,
        );
    }

    public function content(): Content
    {
        $ad = \App\Models\EmailSponsorAd::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->inRandomOrder()
            ->first();

        return new Content(
            view: 'emails.newEventCreated',
            with: [
                'event' => $this->event,
                'organizer' => $this->organizer,
                'recipient' => $this->recipient,
                'trackingToken' => $this->trackingToken,
                'ad' => $ad,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
