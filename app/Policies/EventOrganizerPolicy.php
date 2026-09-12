<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventOrganizerPolicy extends Policy
{
    use HandlesAuthorization;

    protected $module = "event_organizers";
}
