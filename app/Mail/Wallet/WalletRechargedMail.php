<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletRechargedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $amount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Wallet Recharged Successfully - Link Up',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.wallet-recharged',
            with: [
                'user' => $this->user,
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
