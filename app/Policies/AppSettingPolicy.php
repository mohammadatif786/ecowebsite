<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppSettingPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'app_settings';
}
