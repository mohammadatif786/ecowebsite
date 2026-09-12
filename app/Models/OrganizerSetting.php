<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'show_venues_map',
        'show_followers',
        'show_reviews',
        'show_stats',
        'tap_checkin',
    ];

    protected $casts = [
        'show_venues_map' => 'boolean',
        'show_followers' => 'boolean',
        'show_reviews' => 'boolean',
        'show_stats' => 'boolean',
        'tap_checkin' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
