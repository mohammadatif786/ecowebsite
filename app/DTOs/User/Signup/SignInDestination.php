<?php

namespace App\DTOs\User\Signup;

/**
 * Where a member goes right after a correct password.
 */
enum SignInDestination: string
{
    /** Signed up on the website but the email code was never entered. */
    case VerifyEmail = 'verify-email';

    /** Fully signed in; continue to the app. */
    case App = 'app';

    /** Existing member whose phone is not verified: signed out until the SMS code is confirmed. */
    case PhoneCode = 'phone-code';
}
