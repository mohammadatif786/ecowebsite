<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCoins extends Model
{
    protected $fillable = [
        'sender_id',
        'recieved_id',
        'name',
        'coins',
        'status',
        'viewed_at',
        'responded_at',
    ];

    protected $dates = [
        'viewed_at',
        'responded_at',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'recieved_id');
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }
}


