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

    protected $fillable = ['created_by', 'publisher_type', 'publisher_id', 'caption', 'location_name', 'location_place_id', 'latitude', 'longitude', 'allow_coin_gifts', 'visibility', 'status', 'published_at', 'archived_at'];

    protected function casts(): array
    {
        return [
            'allow_coin_gifts' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'visibility' => VibeVisibility::class,
            'status' => VibeStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
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
