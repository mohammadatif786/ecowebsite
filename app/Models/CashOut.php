<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    protected $fillable = [
        'firebase_id',
        'cash_out_id',
        'cash_out_amount',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {

                // Search in related user's first_name
                $q->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', '%' . $search . '%')
                        || $q2->where('last_name', 'like', '%' . $search . '%');
                });
            });
        });
    }
}
