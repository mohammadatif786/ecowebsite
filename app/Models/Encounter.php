<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
    protected $fillable = [
        'firebase_id',
        'date',
        'from',
        'to',
        'liked',
        'seen',
    ];
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('from', 'like', '%' . $search . '%')
                ->orWhere('to', 'like', '%' . $search . '%');
        });
    }
}
