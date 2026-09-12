<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'firebase_id',
        'doc_id',
        'from_email',
        'from_name',
        'from_phone',
        'from_address',
        'for_name',
        'for_email',
        'for_phone',
        'for_address',
        'notes',
        'sequence',
        'collection_name',
    ];
    public function advertisements()
    {
        return $this->hasOne(Advertisement::class, 'invoice_id');
    }
    public function restaurants()
    {
        return $this->hasOne(Restaurant::class, 'invoice_id');
    }
    public function clubFetes()
    {
        return $this->hasOne(ClubFete::class, 'invoice_id');
    }
}
