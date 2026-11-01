<?php

namespace App\Policies;

use App\Domain\Vibes\Enums\VibePublisherType;
use App\Models\UserCustomPublisher;
use App\Models\ClubFete;
use App\Models\OrganizerProfile;
use App\Models\User;

class VibePolicy
{
    /**
     * Whether this account may post at all: it must not be disabled (`users.status`).
     *
     * `users.is_active` is deliberately NOT part of this rule. It is an online/offline flag —
     * logging out sets it to 0 (with `last_active`), it starts at 0 for a new account and the
     * website's login never sets it back — so using it here blocked every member who was
     * simply "offline" from posting.
     */
    public function create(User $user): bool
    {
        return ! in_array($user->getAttribute('status'), [false, 0, '0'], true);
    }

    /**
     * Why `publishAs` says no, in words the member can act on — null when posting is allowed.
     * (The rule itself is unchanged; this only lets the screen say which part failed.)
     */
    public function publishDenial(User $user, VibePublisherType $type, int $publisherId): ?string
    {
        if (! $this->create($user)) {
            return 'Your account is disabled, so it can’t post. Please contact support.';
        }

        if ($this->publishAs($user, $type, $publisherId)) {
            return null;
        }

        return match ($type) {
            VibePublisherType::User => 'Reload the page and try again — the post must be published with your own account.',
            VibePublisherType::Organization, VibePublisherType::CustomPublisher => 'That organization is not on your account — choose “Myself” or add the organization again.',
            VibePublisherType::Group => 'Only a group’s owner or an admin can post as that group.',
        };
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
            VibePublisherType::CustomPublisher => UserCustomPublisher::query()
                ->whereKey($publisherId)->where('user_id', $user->getKey())->exists(),
        };
    }
}
