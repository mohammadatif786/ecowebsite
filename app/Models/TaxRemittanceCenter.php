<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class TaxRemittanceCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_name',
        'jurisdiction',
        'payment_method',
        'routing_number',
        'account_number',
        'api_base',
        'api_key',
        'api_secret',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    // Mutators to encrypt sensitive data before saving
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = $value ? Crypt::encrypt($value) : null;
    }

    // Accessors to decrypt sensitive data when accessed
    public function getApiKeyAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function getApiSecretAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }
}
