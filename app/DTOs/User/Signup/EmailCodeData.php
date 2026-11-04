<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * A member and the 6-digit code they typed on the Verify email page.
 */
final readonly class EmailCodeData
{
    public function __construct(
        public User $user,
        public string $code,
    ) {}

    /**
     * @param  array{code: string}  $validated
     */
    public static function fromValidated(User $user, array $validated): self
    {
        return new self($user, preg_replace('/\D/', '', (string) $validated['code']));
    }
}
