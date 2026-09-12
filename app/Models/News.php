<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'firebase_id',
        'image_object',
        'title',
        'summary',
        'content',
        'status',
        'country',
        'country_code',
        'region',
        'category',
        'source_id',
        'source_type',
        'source_name',
        'published_at',
        'is_breaking',
        'breaking_expires_at',
        'trending',
        'media',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'published_at' => 'datetime',
        'breaking_expires_at' => 'datetime',
        'is_breaking' => 'boolean',
        'status' => 'boolean',
        'media' => 'array',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('title', 'like', '%' . $search . '%');
        });
    }

    public function source()
    {
        return $this->belongsTo(NewsSource::class);
    }
}
