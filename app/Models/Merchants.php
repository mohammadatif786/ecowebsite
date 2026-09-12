<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchants extends Model
{
    protected $table = 'merchants';

    protected $fillable = ['merchant_type', 'name', 'owner_name', 'country', 'state', 'city', 'pickup_locations', 'offers_pickup', 'offers_delivery', 'business_license', 'vat_certificate', 'business_license_file', 'vat_certificate_file', 'is_active', 'user_id', 'phone'];

    protected $casts = [
        'pickup_locations' => 'array',
        'is_active' => 'boolean',
        'offers_pickup' => 'boolean',
        'offers_delivery' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
