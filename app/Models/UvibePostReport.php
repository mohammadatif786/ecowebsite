<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A member's report of a U Vibe post, for the moderation team. */
class UvibePostReport extends Model
{
    protected $table = 'uvibe_post_reports';

    protected $fillable = ['vibe_id', 'reporter_id', 'author_id', 'reason_key', 'reason', 'severity', 'notes', 'excerpt', 'campus', 'has_media', 'status'];

    protected function casts(): array
    {
        return ['has_media' => 'boolean'];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
