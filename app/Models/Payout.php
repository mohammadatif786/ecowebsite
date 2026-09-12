<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'method',
        'author',
        'destination',
        'status',
        'firebase_id',
        'event_id',
        'reference',
        'organizer_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function scopeFilter($query, array $filters)

    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('author', 'like', '%' . $search . '%');
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });
    }

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
