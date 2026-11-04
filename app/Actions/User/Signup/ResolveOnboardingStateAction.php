<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\OnboardingStage;
use App\DTOs\User\Signup\OnboardingState;
use App\Models\User;

/**
 * Single source of truth for "how far through sign-up is this member".
 *
 * Every gate (middleware, controllers, the final wizard save) asks this class, so the rules live
 * in one place:
 *   1. Email confirmed (only required for accounts made by the new sign-up).
 *   2. Wizard finished. Its phone step ends with a verified phone, checked again at the final save.
 */
class ResolveOnboardingStateAction
{
    public function execute(User $user): OnboardingState
    {
        $emailVerified = $user->email_verified_at !== null;
        $phoneVerified = $user->phone_verified_at !== null;

        // Sign-up is for members. Organizers and admins have their own entry points.
        if ($user->type != 'user') {
            return new OnboardingState(OnboardingStage::Complete, $emailVerified, $phoneVerified);
        }

        if ((bool) $user->requires_email_verification && ! $emailVerified) {
            return new OnboardingState(OnboardingStage::Email, $emailVerified, $phoneVerified);
        }

        if (! (bool) $user->is_wizard_completed) {
            return new OnboardingState(OnboardingStage::Profile, $emailVerified, $phoneVerified);
        }

        return new OnboardingState(OnboardingStage::Complete, $emailVerified, $phoneVerified);
    }
}
