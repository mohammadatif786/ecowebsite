<?php

namespace App\DTOs\User\Signup;

/**
 * The result of ContinueSignInAction. The controller turns it into a redirect.
 */
final readonly class SignInOutcome
{
    public function __construct(
        public SignInDestination $destination,
        /** Encrypted member id for the phone code screen. Only set for SignInDestination::PhoneCode. */
        public ?string $phoneCodeToken = null,
    ) {}
}
