<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'firbase_id',
        'note',
        'balance',
        'credit',
        'debit',
        'user_id',
        'from_user_id',
        'to_user_id',
        'transaction_type',
        'ref_transaction_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
    ];
}
