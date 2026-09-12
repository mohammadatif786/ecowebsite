<?php

namespace App\Mail;

use App\Models\TicketSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMessageToAttendeeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected TicketSale $attendee;
    private string $mailSubject;
    protected string $body;

    /**
     * Create a new message instance.
     */
    public function __construct(TicketSale $attendee, string $subject, string $body)
    {
        $this->attendee = $attendee;
        $this->mailSubject = $subject;
        $this->body = $body;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sendMessageToAttendee',
            with: [
                'attendee' => $this->attendee,
                'body' => $this->body,
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
