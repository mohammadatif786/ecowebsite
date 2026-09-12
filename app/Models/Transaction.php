<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use O21\LaravelWallet\Contracts\TransactionContract;
use O21\LaravelWallet\Enums\TransactionStatus;
use O21\LaravelWallet\Traits\InteractsWithTransaction;

class Transaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'amount',
        'commission',
        'received',
        'currency',
        'status',
        'processor_id',
        'meta',
        'archived',
        'invisible',
        'batch',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'float',
        'commission' => 'float',
        'received' => 'float',
        'created_at' => 'datetime',
    ];
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('uuid', 'like', '%' . $search . '%')
                ->orWhere('from_id', 'like', '%' . $search . '%')
                ->orWhere('to_id', 'like', '%' . $search . '%');
        });
    }
    public function from()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the receiver model (polymorphic).
     */
    public function to()
    {
        return $this->belongsTo(User::class);
    }
}
