<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryPhoneCode extends Model
{
    protected $fillable = [
        'code',
        'name'
    ];
}
