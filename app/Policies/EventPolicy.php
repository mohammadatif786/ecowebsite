<?php

namespace App\Policies;

use App\Models\LinkUpEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    protected $module = 'events';

    public function viewAttendees(User $user, LinkUpEvent $event): bool
    {
        return $user->id === $event->user_id
            || ($event->organizer_id && $user->organizer_id === $event->organizer_id)
            || (bool) ($user->is_admin ?? false);
    }
}
