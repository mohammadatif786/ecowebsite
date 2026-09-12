<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payout;
    public $organizerContact;

    /**
     * Create a new message instance.
     */
    public function __construct($payout, $organizerContact)
    {
        $this->payout = $payout;
        $this->organizerContact = $organizerContact;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LinkUp Payout Confirmation - ' . $this->payout->reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payoutEmail',
            with: [
                'payout' => $this->payout,
                'organizerContact' => $this->organizerContact
            ]
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
