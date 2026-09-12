<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerFollower extends Model
{
    protected $fillable = [
        'user_id',
        'organizer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
