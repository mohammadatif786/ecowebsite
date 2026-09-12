<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketExtraSetting extends Model
{
    protected $fillable = [
        'ticket_id',
        'cookout',
        'wellness',
    ];

    protected $casts = [
        'cookout' => 'array',
        'wellness' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}

