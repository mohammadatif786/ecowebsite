<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateLiveStreamSub extends Model
{
    protected $fillable = ['user_streamer_id', 'pay_user_id', 'stream_id', 'payment_type', 'pay_amount', 'status', 'stripe_session_id'];
}
