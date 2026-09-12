<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'paypal_id',
        'bank_name',
        'account_number',
        'routing_number',
        'account_type',
        'is_default',
    ];

    protected $casts = [
        'paypal_id' => 'encrypted',
        'bank_name' => 'encrypted',
        'account_number' => 'encrypted',
        'routing_number' => 'encrypted',
        'account_type' => 'encrypted',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
