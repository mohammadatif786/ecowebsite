<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VibeAttachment extends Model
{
    protected $fillable = ['vibe_id', 'attachable_type', 'attachable_id'];

    public function vibe()
    {
        return $this->belongsTo(Vibe::class);
    }

    public function attachable()
    {
        return $this->morphTo();
    }
}
