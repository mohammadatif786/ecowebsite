<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EventOrganizer extends Model
{
    protected $fillable = [
        'firebase_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'role',
        'address',
        'website',
        'radio',
        'kyc_status', // Default status
        'password', // Optional, if needed
        'passport_photo', // Optional, if needed
        'driver_license_photo', // Optional, if needed
    ];

    public function scopeFilter($query, array $filters)
    {
      $query->when($filters['search'] ?? null, function ($query, $search) {
    $query->where(function ($q) use ($search) {
        $q->orWhereHas('user', function ($q2) use ($search) {
            $q2->where(
                DB::raw("CONCAT(first_name, ' ', last_name)"),
                'like',
                '%' . $search . '%'
            );
        });
    });
});
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
