<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TicketSale;

class TicketResendMail extends Mailable
{
    use Queueable, SerializesModels;

    public TicketSale $ticket;

    public function __construct(TicketSale $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this
            ->subject('Your E‑Ticket for ' . ($this->ticket->event->title ?? 'Event'))
            ->view('emails.tickets.resend')
            ->with([
                'ticket' => $this->ticket,
                'user' => $this->ticket->user,
                'event' => $this->ticket->event,
                'appURL' => asset('storage') . '/',
                'app_url' => config('app.url'),
            ]);
    }
}
