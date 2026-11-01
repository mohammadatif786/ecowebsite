<?php

namespace App\Models;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Domain\Vibes\Enums\VibeVisibility;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vibe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['created_by', 'publisher_type', 'publisher_id', 'caption', 'title', 'category', 'campus', 'university_id', 'price', 'is_uvibe', 'show_on_vibes', 'text_bg', 'text_font', 'text_style', 'posted_role', 'as_university', 'location_name', 'location_place_id', 'latitude', 'longitude', 'allow_coin_gifts', 'visibility', 'status', 'published_at', 'archived_at', 'reposted_from_id', 'reposted_from_name', 'edited_at'];

    protected function casts(): array
    {
        return [
            'allow_coin_gifts' => 'boolean',
            'is_uvibe' => 'boolean',
            'show_on_vibes' => 'boolean',
            'as_university' => 'boolean',
            'text_style' => 'array',
            'price' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'visibility' => VibeVisibility::class,
            'status' => VibeStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher()
    {
        return $this->morphTo();
    }

    public function media()
    {
        return $this->hasMany(VibeMedia::class)->orderBy('sort_order');
    }

    public function attachments()
    {
        return $this->hasMany(VibeAttachment::class);
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'attachable', 'vibe_attachments');
    }

    public function events()
    {
        return $this->morphedByMany(LinkUpEvent::class, 'attachable', 'vibe_attachments');
    }

    public function likes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }

    public function authUserLike()
    {
        return $this->morphOne(Vote::class, 'votable')->where('user_id', auth()->id())->where('type', 'like');
    }

    public function comments()
    {
        return $this->hasMany(VibeComment::class);
    }
}
