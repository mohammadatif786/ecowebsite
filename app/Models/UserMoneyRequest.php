<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserMoneyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'amount',
        'note',
        'status',
    ];

    // Who sent the request
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    // Who received the request
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
