<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class FlaggedUser extends Model
{
    protected $fillable = [
        'uid',
        'from_user_id',
        'to_user_id',
        'from_first_name',
        'from_last_name',
        'to_first_Name',
        'to_last_name',
        'message',
        'status',
        'reason',
        'selected_reason'
    ];

    protected $casts = [
        'status' => 'boolean',
        'selected_reason' => 'array',
    ];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

public function scopeFilter($query, array $filters)
{
     $query->when($filters['search'] ?? null, function ($query, $search) {
        $query->where(DB::raw("CONCAT(to_first_name, ' ', to_last_name)"), 'like', '%' . $search . '%');
    });
}


    // model oriented events 
    protected static function booted()
    {
        static::creating(function ($flaggedUser) {
            $flaggedUser->uid = Str::random(28);
        });
    }
}
