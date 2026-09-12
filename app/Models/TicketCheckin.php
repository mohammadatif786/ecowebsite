<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCheckin extends Model
{
    protected $fillable = [
        'ticket_sale_id',
        'scanner_id',
        'event_id',
        'ticket_qrcode_id',
        'scanned_by_email',
        'checked_in_at',
        'notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function ticketSale()
    {
        return $this->belongsTo(TicketSale::class, 'ticket_sale_id');
    }

    public function scanner()
    {
        return $this->belongsTo(ScanSignUser::class, 'scanner_id');
    }

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }
}
