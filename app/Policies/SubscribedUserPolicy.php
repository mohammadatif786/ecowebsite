<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscribedUserPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'events';
}
