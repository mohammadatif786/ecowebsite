<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdsSettingPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ads_settings';
}
