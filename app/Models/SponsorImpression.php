<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorImpression extends Model
{
    protected $fillable = [
        'email_sponsor_ad_id',
        'user_id',
        'email_category',
        'country_code',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
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
