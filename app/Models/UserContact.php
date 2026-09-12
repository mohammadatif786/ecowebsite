<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_user_id',
        'name',
    ];

    // The owner of the contact
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The actual contact (another user)
    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }
}
