<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WithdrawPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ewallet_withdraw_requests';
}
