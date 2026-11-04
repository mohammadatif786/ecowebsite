<?php

namespace App\DTOs\User\Signup;

/**
 * What "Create account" sends. Built from a validated request, never from raw input.
 */
final readonly class RegisterMemberData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $linkupId,   // stored with the leading @
        public string $password,   // plain; the User model's `hashed` cast hashes it
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, linkup_id: string, password: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            firstName: trim($validated['first_name']),
            lastName: trim($validated['last_name']),
            email: strtolower(trim($validated['email'])),
            linkupId: '@'.ltrim(strtolower(trim($validated['linkup_id'])), '@'),
            password: $validated['password'],
        );
    }

    public function fullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
