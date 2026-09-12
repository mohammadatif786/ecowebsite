<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    protected $fillable = [
        'firebase_id',
        'uid',
        'user_id',
        'routing_Number',
        'cash_App_Id',
        'bank_Name',
        'payPal_id',
        'bank_Account',
        'zell_Id',
    ];
}
