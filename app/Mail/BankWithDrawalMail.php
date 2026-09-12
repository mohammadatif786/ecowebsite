<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankWithDrawalMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $mailData;
    /**
     * Create a new message instance.
     */
    public function __construct(array $mailData)
     {
         $this->mailData = $mailData;
     }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bank Withdrawal Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bank_withdrawal_notification',
            with: [
                'user_name' => $this->mailData['user_name'] ?? 'User',
                'total_amount' => $this->mailData['total_amount'] ?? 'N/A',
                'fee_amount' => $this->mailData['fee_amount'] ?? 'N/A',
                'payout_amount' => $this->mailData['payout_amount'] ?? 'N/A',
                'bank_name' => $this->mailData['bank_name'] ?? 'Unknown Bank',
                'account_number' => $this->mailData['account_number'] ?? 'Unknown Account',
                'note' => $this->mailData['note'] ?? '',
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
