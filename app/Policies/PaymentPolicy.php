<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'payments';
}
