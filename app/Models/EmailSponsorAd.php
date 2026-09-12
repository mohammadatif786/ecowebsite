<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSponsorAd extends Model
{
    protected $fillable = [
        'company_name',
        'email_ad_category_id',
        'start_date',
        'end_date',
        'priority',
        'status',
        'headline',
        'message',
        'cta_text',
        'cta_url',
        'image',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'priority' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset('storage/' . $this->image);
    }

    public function category()
    {
        return $this->belongsTo(EmailAdCategory::class, 'email_ad_category_id');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'email_sponsor_ad_country');
    }

    public function impressions()
    {
        return $this->hasMany(SponsorImpression::class);
    }

    public function clicks()
    {
        return $this->hasMany(SponsorClick::class);
    }
}
