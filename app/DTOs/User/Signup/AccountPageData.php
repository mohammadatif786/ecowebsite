<?php

namespace App\DTOs\User\Signup;

/**
 * What the combined Create account / Sign in page needs from the server.
 * Both /register and /login render the same page; only the starting tab differs.
 */
final readonly class AccountPageData
{
    public const MODE_CREATE = 'create';

    public const MODE_SIGN_IN = 'signin';

    private function __construct(public string $mode) {}

    public static function create(): self
    {
        return new self(self::MODE_CREATE);
    }

    public static function signIn(): self
    {
        return new self(self::MODE_SIGN_IN);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'status' => session('status'),
            'urls' => [
                'register' => route('register'),
                'login' => route('login'),
                'checkLinkupId' => route('check.linkup.id'),
                'forgotPassword' => route('password.request'),
                'terms' => route('legal.terms'),
                'privacy' => route('legal.privacy'),
                'acceptableUse' => route('legal.acceptable-use'),
            ],
        ];
    }
}
