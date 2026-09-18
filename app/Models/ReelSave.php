<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelSave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_reel_id',
        'user_id',
    ];

    public function reel(): BelongsTo
    {
        return $this->belongsTo(UserReel::class, 'user_reel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
