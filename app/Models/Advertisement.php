<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'firebase_id',
        'invoice_id',
        'category',
        'name',
        'headline',
        'description',
        'country',
        'state',
        'city',
        'location',
        'email',
        'phone',
        'ad_type',
        'image',
        'video',
        'thumbnail',
        'max_duration',
        'autoplay_sound',
        'cta_overlay_timing',
        'loop_video',
        'cta_text',
        'brand_color',
        'www',
        'cost',
        'paid',
        'is_paid',
        'status',
        'start_date',
        'end_date',
        'duration_days',
        'price_package',
        'custom_cost_override',
        'payment_ref',
        'publication_status',
        'targeting_notes',
    ];
    protected $appends = ['image_url', 'video_url'];
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }
    public function getVideoUrlAttribute()
    {
        return $this->video ? asset($this->video) : null;
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * All swipe-card interaction events tracked for this ad
     * (impressions, clicks, swipe_left).
     */
    public function swipeAdEvents()
    {
        return $this->hasMany(\App\Models\SwipeAdEvent::class, 'advertisement_id');
    }
}
