<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'firebase_id',
        'invoice_id',
        'name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'location',
        'www',
        'image_object',
        'video_object',
        'paid',
        'is_paid',
        'cost',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'cost' => 'decimal:2',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
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
}
