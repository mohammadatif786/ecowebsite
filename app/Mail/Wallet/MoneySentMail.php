<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MoneySentMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    public function __construct(
        public User $sender,
        public User $recipient,
        public int $amount,
        public ?string $note = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Money Sent Successfully - Link Up',
        );
    }

    public function content(): Content
    {
        $ad = $this->getSponsorAd('payment_sent', $this->recipient->country_code);

        return new Content(
            view: 'emails.wallet.money-sent',
            with: [
                'sender' => $this->sender,
                'recipient' => $this->recipient,
                'amount' => $this->amount,
                'note' => $this->note,
                'recipient_avatar' => $this->recipient->avatar,
                'formattedAmount' => '$' . number_format($this->amount, 2),
                'ad' => $ad,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
