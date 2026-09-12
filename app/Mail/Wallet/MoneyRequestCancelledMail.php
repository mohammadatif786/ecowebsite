<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MoneyRequestCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $requester,
        public User $recipient,
        public int $amount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Money Request Cancelled - Link Up',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.money-request-cancelled',
            with: [
                'requester' => $this->requester,
                'recipient' => $this->recipient,
                'amount' => $this->amount,
                'formattedAmount' => '$' . number_format($this->amount, 2),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
