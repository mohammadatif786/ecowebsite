<?php

namespace App\Mail\Wallet;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MoneyRequestReceivedMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected string $appUrl;

    public function __construct(
        public User $requester,
        public User $recipient,
        public int $amount,
        public ?string $note = null
    ) {
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Money Request Received - Link Up',
        );
    }

    public function content(): Content
    {
        $requesterAvatarUrl = $this->requester->image
            ? (str_starts_with($this->requester->image, 'http')
                ? $this->requester->image
                : $this->appUrl . 'storage/' . $this->requester->image)
            : $this->appUrl . 'assets/images/default-avatar.png';

        $ad = $this->getSponsorAd('money_request', $this->recipient->country_code);

        return new Content(
            view: 'emails.wallet.money-request-received',
            with: [
                'requester' => $this->requester,
                'recipient' => $this->recipient,
                'amount' => $this->amount,
                'note' => $this->note,
                'formattedAmount' => 'B$' . number_format($this->amount, 2),
                'date' => now()->format('F j, Y'),
                'time' => now()->format('g:i A'),
                'logoUrl' => $this->appUrl . 'assets/images/logo.png',
                'walletRequestImageUrl' => $this->appUrl . 'assets/images/money-request-wallet.png',
                'requesterAvatarUrl' => $requesterAvatarUrl,
                'ad' => $ad,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
