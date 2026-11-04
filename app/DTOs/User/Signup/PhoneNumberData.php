<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * A phone number the member wants a code sent to, already normalised to +<digits>.
 */
final readonly class PhoneNumberData
{
    public function __construct(
        public User $user,
        public string $phone,
    ) {}

    /**
     * @param  array{phone_number: string}  $validated
     */
    public static function fromValidated(User $user, array $validated): self
    {
        return new self($user, self::normalise($validated['phone_number']));
    }

    /**
     * "+1 (876) 123-4567" -> "+18761234567". A number typed without "+" gets one, as the
     * existing sign-in OTP does, after dropping leading zeros.
     */
    public static function normalise(string $raw): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', trim($raw)) ?? '';

        return str_starts_with($cleaned, '+') ? $cleaned : '+'.ltrim($cleaned, '0');
    }

    /** Digits only, for comparing two numbers written differently. */
    public static function digits(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?? '';
    }
}
