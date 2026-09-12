<?php

namespace App\Mail;

use App\Models\LinkUpEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyTicketEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $ticketsale;
    public LinkUpEvent $event;
    public $app_url;
    public $user_data;
    public $sponsors;

    public function __construct($ticket_sale, LinkUpEvent $event, ?string $app_url, $user_data = null, $sponsors = null)
    {
        $this->ticketsale = $ticket_sale;
        $this->event = $event;
        $this->app_url = $app_url;
        $this->user_data = $user_data;
        $this->sponsors = $sponsors;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Buy Ticket Email Mail',
        );
    }

    /**
     * 
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.buyTicketEmail',
            with:[
                'ticket_sale' => $this->ticketsale,
                'event' => $this->event,
                'app_url' => $this->app_url,
                'user_data' => $this->user_data,
                'sponsors' => $this->sponsors,
            ]
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
