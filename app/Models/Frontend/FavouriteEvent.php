<?php

namespace App\Models\Frontend;

use App\Models\LinkUpEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FavouriteEvent extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }
}
