<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'email';
}
