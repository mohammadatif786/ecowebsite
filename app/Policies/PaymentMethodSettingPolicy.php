<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodSettingPolicy
{
    use HandlesAuthorization;

    protected $module = 'payment_method_settings';
}
