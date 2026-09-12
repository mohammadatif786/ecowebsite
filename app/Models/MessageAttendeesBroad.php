<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttendeesBroad extends Model
{
    protected $fillable = [
        'event_id',
        'audience',
        'preset',
        'subject',
        'body',
        'chLinkUp',
        'chEmail',
        'chSMS',
    ];
}
