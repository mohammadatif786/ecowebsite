<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessSlotBlock extends Model
{
    protected $fillable = [
        'ticket_id',
        'slot_date',
        'start_time',
        'end_time',
        'count',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'count' => 'integer',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}

