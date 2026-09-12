<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class SelectedAttendee extends Model
{
    protected $fillable = [
        'firebase_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'address',
        'event_id',
        'org_id',
        'scanner_image_object',
    ];

    protected $casts = [
        //
    ];
}
