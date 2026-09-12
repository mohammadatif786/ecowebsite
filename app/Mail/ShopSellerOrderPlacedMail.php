<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopSellerOrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $sellerItems
     */
    public function __construct(
        public Order $order,
        public User $seller,
        public User $buyer,
        public array $sellerItems,
        public array $deliveryDetails,
        public ?string $trackingToken = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New order placed: ' . ($this->order->number ?? ('Order #' . $this->order->id)),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shopSellerOrderPlaced',
            with: [
                'order' => $this->order,
                'seller' => $this->seller,
                'buyer' => $this->buyer,
                'sellerItems' => $this->sellerItems,
                'deliveryDetails' => $this->deliveryDetails,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
