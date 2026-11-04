<?php

namespace App\Actions\User\Signup;

use App\Actions\RegisteredUserAction;
use App\DTOs\User\Signup\LinkupIdAvailability;

/**
 * Is this LinkUp ID free to take? Reserved words, repeated characters and taken IDs are all
 * "no". The rules live in RegisteredUserAction so the website and the mobile app agree.
 */
class CheckLinkupIdAction
{
    public function __construct(private RegisteredUserAction $legacy) {}

    public function execute(string $linkupId): LinkupIdAvailability
    {
        $handle = ltrim(strtolower(trim($linkupId)), '@');

        return LinkupIdAvailability::fromLegacy(
            $this->legacy->validateLinkUpId((object) ['linkup_id' => $handle])
        );
    }
}
