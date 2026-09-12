<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    protected $table = 'conversation_users';
    protected $fillable = [
        'user_id',
        'conversation_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation()
    {
        return $this->belongsTo(User::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class, 'sender_id', 'user_id');
    }
}
