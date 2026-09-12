<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'restaurants';
}
