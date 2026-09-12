<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorClick extends Model
{
    protected $fillable = [
        'email_sponsor_ad_id',
        'user_id',
        'ip_address',
        'user_agent',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(EmailSponsorAd::class, 'email_sponsor_ad_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
