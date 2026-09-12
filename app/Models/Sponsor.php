<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Sponsor extends Model
{
    protected $fillable = [
        'sponsor_image_object',
        'name',
        'description',
        'image_object',
        'status',
        'link_up_event_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
            $query->orWhere('description', 'like', '%' . $search . '%');
        });
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
