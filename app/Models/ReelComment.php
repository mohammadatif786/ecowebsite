<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ReelComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_reel_id',
        'user_id',
        'parent_id',
        'comment',
    ];

    public function reel(): BelongsTo
    {
        return $this->belongsTo(UserReel::class, 'user_reel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ReelComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReelComment::class, 'parent_id');
    }

    /** Likes on this comment (same `votes` table vibe comments use). */
    public function likes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }
}
