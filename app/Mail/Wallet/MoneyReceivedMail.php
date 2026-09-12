<?php

namespace App\Mail\Wallet;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MoneyReceivedMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected string $appUrl;
    protected Transaction $transaction;


    public function __construct(
        public User $sender,
        public User $recipient,
        public int $amount,
        public int $walletBalanceAfter,
        public ?string $note = null
    ) {
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';

        $this->transaction = Transaction::where('from_id', $sender->id)
            ->where('to_id', $recipient->id)
            ->latest()
            ->firstOrFail();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Money Received - Link Up',
        );
    }

    public function content(): Content
    {
        $ad = $this->getSponsorAd('money_received', $this->recipient->country_code);

        return new Content(
            view: 'emails.wallet.money-received',
            with: [
                'sender' => $this->sender,
                'recipient' => $this->recipient,
                'amount' => $this->amount,
                'note' => $this->note,
                'date' => now()->format('F j, Y'),
                'time' => now()->format('g:i A'),
                'transaction_id' => $this->transaction->uuid,
                'walletBalanceAfter' => $this->walletBalanceAfter,
                'logoUrl' => $this->appUrl . 'assets/images/logo.png',
                'avatarUrl' => $this->appUrl . 'storage/' . 'avatars/' . 'logomoneyrecived.png',
                'ad' => $ad,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
