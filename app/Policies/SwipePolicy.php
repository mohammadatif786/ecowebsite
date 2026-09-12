<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SwipePolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'swipes';
}
