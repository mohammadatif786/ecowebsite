<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LinkUpEvent;
class EventDetails extends Model
{
    protected $table = 'event_details';

    protected $fillable = [
        'event_id',
        'scanner_id',
        'media',
        'audiences',
        'attendees',
        'enable_views',
        'seating_plan',
        'event_type',
        'single_event',
        'single_event_date',
        'single_start_time',
        'single_end_time',
        'recurr_pattern',
        'recurr_start_date',
        'recurr_end_date',
        'image_gallery',
        'artists',
        'artist_image',
        'twitter',
        'instagram',
        'facebook',
        'tiktok',
        'linkedin',
        'zip',
        'tax_rate',
        'tax_included',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'media' => 'array',
        'audiences' => 'array',
        'image_gallery' => 'array',
        'artists' => 'array',
        'artist_image' => 'array',

        'attendees' => 'boolean',
        'enable_views' => 'boolean',
        'seating_plan' => 'boolean',
        'single_event' => 'boolean',

        'single_event_date' => 'date',
        'recurr_start_date' => 'date',
        'recurr_end_date' => 'date',
        'single_start_time' => 'datetime:H:i',
        'single_end_time' => 'datetime:H:i',
        'scanner_id' => 'array',
    ];

    /**
     * Relations
     */
    public function event()
    {
        return $this->belongsTo(LinkUpEvent::class, 'event_id');
    }

    public function scanner()
    {
        return $this->belongsTo(ScanSignUser::class, 'scanner_id');
    }
}
