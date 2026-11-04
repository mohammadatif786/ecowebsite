<?php

namespace App\DTOs\User\Signup;

/**
 * Where a member is in sign-up. A member only ever moves forward: Email -> Profile -> Complete.
 */
enum OnboardingStage: string
{
    /** Account exists, email not confirmed yet. Only the Verify email page is reachable. */
    case Email = 'email';

    /** Email confirmed, wizard not finished (phone code is step 3 of it). */
    case Profile = 'profile';

    /** Everything done: the rest of LinkUp is open. */
    case Complete = 'complete';
}
