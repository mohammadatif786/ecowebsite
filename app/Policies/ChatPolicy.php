<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'chats';
}
