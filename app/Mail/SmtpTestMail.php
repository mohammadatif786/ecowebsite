<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected string $fromAddress, protected string $fromName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: 'LinkUp SMTP Test Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom',
            with: [
                'subject' => 'LinkUp SMTP Test Email',
                'content' => 'This is a test email confirming your SMTP settings are working correctly. Sent at ' . now()->toDayDateTimeString() . '.',
            ],
        );
    }
}
