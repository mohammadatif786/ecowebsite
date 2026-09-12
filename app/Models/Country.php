<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'subregion', 'flag', 'currency', 'support_email'];

    public function emailAds()
    {
        return $this->belongsToMany(EmailSponsorAd::class, 'email_sponsor_ad_country');
    }
}
