<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoinsReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $sender,
        public User $recipient,
        public int $amount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Coins Received - Link Up',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.coins-received',
            with: [
                'sender' => $this->sender,
                'recipient' => $this->recipient,
                'amount' => $this->amount,
                'formattedAmount' => number_format($this->amount) . ' coins',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
