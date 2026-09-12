<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asue extends Model
{

    protected $table = 'asues';


    protected $fillable = [
        'user_id',
        'name',
        'frequency',
        'hand_amount',
        'start_date',
        'max_members',
        'note',
        'status',
        'asue_unique_code',
        'current_turn',
        'payout_accepted'
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function invitedUsers()
    {
        return $this->belongsToMany(User::class, 'asue_invites', 'asue_id', 'user_id')
                    ->withPivot('position', 'participation_status')
                    ->withTimestamps();
    }


}
