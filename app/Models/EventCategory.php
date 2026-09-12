<?php

namespace App\Models;

use App\Models\LinkUpEvent;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    // 'image_object',
    protected $fillable = [
        'name',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'boolean',
    ];

    public function linkupEvents()
    {
        return $this->hasMany(LinkUpEvent::class, 'category_id', 'id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }
}
