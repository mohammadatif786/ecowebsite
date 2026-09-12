<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HideSpecificUser extends Model
{
    protected $fillable = [
        'user_id',
        'target_user_id', 
        'is_hidden'
    ];

    protected $casts = [
        'is_hidden' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
