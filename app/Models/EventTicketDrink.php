<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketDrink extends Model
{
    protected $fillable = [
        'type',
        'name',
        'amount',
        'created_by'
    ];

    public function CreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the ticket sale that owns the drink record.
     */
    public function ticketSale()
    {
        return $this->belongsTo(TicketSale::class, 'ticket_sale_id');
    }
}
