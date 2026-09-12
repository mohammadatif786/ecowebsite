<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'subscriptions';
}
