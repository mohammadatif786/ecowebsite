<?php

namespace App\Policies;

use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScanSignUserPolicy
{
    /**
     * Determine whether the user can scan any ticket.
     */
    public function scanTicket(User $user): bool
    {
        return $user->hasRole('organizer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->hasRole('organizer') || $user->hasRole('scanner');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('organizer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScanSignUser $scanSignUser): bool
    {
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();
        return $organizerProfile->id == $scanSignUser->org_id && $user->hasRole('organizer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScanSignUser $scanSignUser): bool
    {
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();
        return $organizerProfile->id == $scanSignUser->org_id && $user->hasRole('organizer');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function assignRole(User $user, ScanSignUser $scanSignUser): bool
    {
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();
        return $organizerProfile->id == $scanSignUser->org_id && $user->hasRole('organizer');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function updateStatus(User $user , ScanSignUser $scanSignUser): bool
    {
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        return $organizerProfile->id == $scanSignUser->org_id && $user->hasRole('organizer');
    }
}
