<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminUserPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'admin_users';
}
