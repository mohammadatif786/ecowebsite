<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerMedia extends Model
{
    use HasFactory;

    protected $table = 'organizer_medias';

    protected $fillable = [
        'organizer_id',
        'logo',
        'cover_photo',
        'profile_photo',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
