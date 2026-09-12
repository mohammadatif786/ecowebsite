<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'call_type',
        'caller_name',
        'caller_picture',
        'caller_uid',
        'channel_id',
        'channel_name',
        'receiver',
        'response',
        'title',
    ];
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('caller_name', 'like', '%' . $search . '%');
        });
    }
}
