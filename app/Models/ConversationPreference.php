<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationPreference extends Model
{
    protected $fillable = ['user_id', 'other_user_id', 'is_pinned'];
    protected $casts = ['is_pinned' => 'boolean'];
}
