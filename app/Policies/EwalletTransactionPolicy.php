<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EwalletTransactionPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ewallet_transactions';
}
