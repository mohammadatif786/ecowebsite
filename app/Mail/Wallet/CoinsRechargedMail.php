<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoinsRechargedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $amount,
        public int $coins
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Coins Purchased Successfully - Link Up',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.coins-recharged',
            with: [
                'user' => $this->user,
                'amount' => $this->amount,
                'coins' => $this->coins,
                'formattedAmount' => '$' . number_format($this->amount, 2),
                'formattedCoins' => number_format($this->coins) . ' coins',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
