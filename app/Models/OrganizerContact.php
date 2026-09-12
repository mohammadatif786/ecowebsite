<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'email',
        'phone',
        'website',
        'country',
        'state',
        'city',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
