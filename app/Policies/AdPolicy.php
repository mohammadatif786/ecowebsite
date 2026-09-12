<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'ads';
}
