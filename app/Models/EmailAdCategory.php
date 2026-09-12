<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAdCategory extends Model
{
    protected $fillable = [
        'key',
        'short_label',
        'label',
        'icon',
        'description',
        'preview_title',
        'preview_subtitle',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function ads()
    {
        return $this->hasMany(EmailSponsorAd::class);
    }
}
