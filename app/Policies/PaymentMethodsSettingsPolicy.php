<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodsSettingsPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'payment_methods_settings';
}
