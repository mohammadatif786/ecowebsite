<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventReview extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'rating',
        'review_text',
        'reviewer_name',
        'status'
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the event that owns the review
     */
    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }

    /**
     * Get the user that wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get approved reviews only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get reviews for a specific event
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}
