<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'bank_name',
        'account_number',
        'routing_number',
        'paypal_id',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
