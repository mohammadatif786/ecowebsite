<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorePolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'sponsore';
}
