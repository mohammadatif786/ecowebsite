<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product,
        public User $recipient,
        public ?string $trackingToken = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New product: ' . ($this->product->name ?? 'Product'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.productCreated',
            with: [
                'product' => $this->product,
                'recipient' => $this->recipient,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
