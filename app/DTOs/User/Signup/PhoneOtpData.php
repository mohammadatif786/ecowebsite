<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * A member and the 6-digit code they typed for their phone.
 */
final readonly class PhoneOtpData
{
    public function __construct(
        public User $user,
        public string $otp,
    ) {}

    /**
     * @param  array{otp: string}  $validated
     */
    public static function fromValidated(User $user, array $validated): self
    {
        return new self($user, preg_replace('/\D/', '', (string) $validated['otp']));
    }
}
