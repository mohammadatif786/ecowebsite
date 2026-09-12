<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'uid',
        'title',
        'description',
        'user_id',
    ];
}
