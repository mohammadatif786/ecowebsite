<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRemittance extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_name',
        'jurisdiction',
        'payment_method',
        'routing_number',
        'account_number',
        'api_url',
        'api_key',
        'api_secret',
        'api_headers',
        'is_active',
    ];

    protected $casts = [
        'api_headers' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];
}
