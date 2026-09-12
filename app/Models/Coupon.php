<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Coupon extends Model
{
    protected $casts = [
        'expiry_date' => 'date',
    ];

    protected $fillable = [
        // 'firebase_id',
        // 'firebase_event_id',
        'image_object',
        'link_up_event_id',
        'code',
        'expiry_date',
        'description',
        'discount',
        'title',
        'discount_type',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('code', 'like', '%' . $search . '%');
            $query->orWhere('title', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        $query->where('expiry_date', '>', now());
    }

    public function event(){
        return $this->belongsTo(LinkUpEvent::class, 'link_up_event_id');
    }

    public function getImageUrlAttribute()
    {
        if (! $this->image_object) return null;
        
        return asset(Storage::url($this->image_object));
    }
}
