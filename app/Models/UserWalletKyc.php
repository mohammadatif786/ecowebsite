<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletKyc extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'dob',
        'id_type',
        'id_number',
        'address',
        'tax_id',
        'kyc_documents',
        'terms_acknowledged',
        'status',
    ];

    protected $casts = [
        'kyc_documents' => 'array',
        'terms_acknowledged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
