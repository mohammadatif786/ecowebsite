<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CancellationRequests extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'ticket_id',
        'event_id',
        'admin_reason',
        'status',
        'refund_amount',
        'deduct_ammount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }

    // Automatically generate UUID on creation
    protected static function booted()
    {
        static::creating(function ($request) {
            if (empty($request->uuid)) {
                $request->uuid = 'ord_' . Str::uuid()->toString();
            }
        });
    }
}
