<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerKyc extends Model
{
    use HasFactory;

    protected $table = 'organizer_k_y_c_s';

    protected $fillable = [
        'organizer_id',
        'passport_front',
        'passport_back',
        'proof_of_address',
        'status',
        'p_front_status',
        'p_back_status',
        'p_o_add_status',
    ];

    public function organizer()
    {
        return $this->belongsTo(OrganizerProfile::class, 'organizer_id');
    }
}
