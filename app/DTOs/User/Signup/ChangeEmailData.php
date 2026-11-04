<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * "Change email" on the Verify email page (only possible while the email is unverified).
 */
final readonly class ChangeEmailData
{
    public function __construct(
        public User $user,
        public string $email,
    ) {}

    /**
     * @param  array{email: string}  $validated
     */
    public static function fromValidated(User $user, array $validated): self
    {
        return new self($user, strtolower(trim($validated['email'])));
    }
}
