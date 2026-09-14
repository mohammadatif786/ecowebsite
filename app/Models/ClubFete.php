<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubFete extends Model
{
    protected $fillable = [
        'firebase_id',
        'invoice_id',
        'is_paid',
        'start_date',
        'end_date',
        'status',
        'cost',
        'email',
        'image_object',
        'location',
        'name',
        'paid',
        'phone',
        'video_object',
        'www',
        'country',
        'state',
        'city',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function likes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }

    public function authUserLike()
    {
        return $this->morphOne(Vote::class, 'votable')->where('user_id', auth()->id())->where('type', 'like');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'club_fete_members')->withPivot(['role', 'is_active'])->withTimestamps();
    }

    public function vibes()
    {
        return $this->morphMany(Vibe::class, 'publisher');
    }
}
