<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'messages';
}
