<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WithdrawalPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ewallet_withdrawal_methods';
}
