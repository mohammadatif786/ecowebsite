<?php

namespace App\Policies;

use App\Domain\Vibes\Enums\VibePublisherType;
use App\Models\ClubFete;
use App\Models\OrganizerProfile;
use App\Models\User;

class VibePolicy
{
    public function create(User $user): bool
    {
        return ! in_array($user->getAttribute('status'), [false, 0, '0'], true)
            && ! in_array($user->getAttribute('is_active'), [false, 0, '0'], true);
    }

    public function publishAs(User $user, VibePublisherType $type, int $publisherId): bool
    {
        if (! $this->create($user)) {
            return false;
        }

        return match ($type) {
            VibePublisherType::User => $publisherId === $user->getKey(),
            VibePublisherType::Organization => OrganizerProfile::query()
                ->whereKey($publisherId)->where('user_id', $user->getKey())->exists(),
            VibePublisherType::Group => ClubFete::query()->whereKey($publisherId)->where('status', true)
                ->whereHas('members', fn ($query) => $query->whereKey($user->getKey())
                    ->where('club_fete_members.is_active', true)
                    ->whereIn('club_fete_members.role', ['owner', 'admin']))->exists(),
        };
    }
}
