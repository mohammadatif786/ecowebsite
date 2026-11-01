<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A member's application to a Campus Gig (a U Vibe post of type "Gigs"). */
class UvibeApplication extends Model
{
    protected $table = 'uvibe_applications';

    protected $fillable = ['vibe_id', 'user_id', 'status', 'paid_amount', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function vibe()
    {
        return $this->belongsTo(Vibe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
