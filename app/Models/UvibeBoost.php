<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Coins a member sent to a U Vibe post as a Boost. */
class UvibeBoost extends Model
{
    protected $table = 'uvibe_boosts';

    protected $fillable = ['vibe_id', 'user_id', 'gift', 'coins'];

    // thanked_at is set only by "Say Thank You", never mass-assigned.
    protected $casts = ['thanked_at' => 'datetime'];

    public function vibe()
    {
        return $this->belongsTo(Vibe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
