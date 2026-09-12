<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClubFetePolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'clubs_fetes';
}
