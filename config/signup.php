<?php

/*
|--------------------------------------------------------------------------
| Sign-up and onboarding
|--------------------------------------------------------------------------
|
| Limits for the email code and phone code steps that every new member passes
| through before they can use the rest of LinkUp. Change the numbers here, not
| in the Actions.
|
*/

return [

    // Six-digit code emailed right after "Create account".
    'email_code' => [
        'length' => 6,
        'ttl_minutes' => 10,
        'max_attempts' => 3,              // wrong guesses per code before it is dead
        'resend_cooldown_seconds' => 60,  // wait between two sends
        'max_sends_per_hour' => 5,        // per member, including "Change email"
    ],

    // Six-digit code texted in wizard step 3. Same table and rules as the sign-in OTP.
    'phone_otp' => [
        'ttl_minutes' => 5,
        'max_attempts' => 3,
        'resend_cooldown_seconds' => 30,
        'max_sends_per_hour' => 5,
    ],

    // Accounts that never verified their email are removed after this many days
    // (signup:purge-unverified), so they do not hold an email or LinkUp ID forever.
    'unverified_purge_days' => 7,

    // Server-side age rule for the birthday step.
    'minimum_age' => 18,
];
