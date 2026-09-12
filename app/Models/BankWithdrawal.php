<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BankWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'fee_percent',
        'fee_amount',
        'payout_amount',
        'bank_name',
        'account_number',
        'status',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'bank_name' => 'encrypted',
        'account_number' => 'encrypted',
        'amount' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
