<?php

namespace App\Domain\Linkup\Services;

use App\Models\User;

class LinkupAccessService
{
    public function canInteract(User $actor, User $target): bool
    {
        return $actor->isNot($target) && $target->isEligibleForLinkup() && ! $actor->friends()->whereKey($target->id)->exists();
    }

    public function canChat(User $actor, User $other): bool
    {
        return $actor->isNot($other)
            && ($actor->friends()->whereKey($other->id)->exists() || $actor->isMatchedWith($other));
    }
}
