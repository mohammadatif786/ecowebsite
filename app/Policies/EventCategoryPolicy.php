<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventCategoryPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = 'event_categories';
}
