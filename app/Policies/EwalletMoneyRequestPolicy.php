<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EwalletMoneyRequestPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ewallet_money_requests';
}
