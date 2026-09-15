<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VibeComment extends Model
{
    use HasFactory;

    protected $fillable = ['vibe_id', 'user_id', 'comment', 'parent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vibe()
    {
        return $this->belongsTo(Vibe::class);
    }

    public function parent()
    {
        return $this->belongsTo(VibeComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(VibeComment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }

    public function authUserLike()
    {
        return $this->morphOne(Vote::class, 'votable')->where('user_id', auth()->id())->where('type', 'like');
    }
}
