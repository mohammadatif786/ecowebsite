<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneralSettingsPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ewallet_general_settings';
}
