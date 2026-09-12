<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PushNotificationPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'push_notifications';
}
