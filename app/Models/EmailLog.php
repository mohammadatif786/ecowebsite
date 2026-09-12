<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'token',
        'email_type',
        'to_email',
        'to_user_id',
        'from_user_id',
        'subject',
        'status',
        'sent_at',
        'opened_at',
        'open_count',
        'meta',
        'error',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];
}
