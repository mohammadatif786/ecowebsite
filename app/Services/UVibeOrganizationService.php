<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCustomPublisher;
use App\Support\UVibeOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The organizations a member posts for on U Vibe (a student union, a club, a committee…), as in
 * the design's "Post as → An organization". They are the member's own list: added once, then
 * picked on any post. Used by the website and the mobile API through the same controller.
 *
 * Posting as the member's university itself needs no entry here — it is offered from the
 * university chosen under "Posting to".
 */
class UVibeOrganizationService
{
    public function list(User $user): Collection
    {
        return UserCustomPublisher::where('user_id', $user->id)->orderBy('name')->get()->map(fn ($p) => $this->present($p));
    }

    /**
     * Add an organization, or return the one the member already has under that name.
     *
     * @return array{0:array, 1:bool} [organization, whether it is new]
     */
    public function add(User $user, string $name, ?string $orgType = null): array
    {
        $name = trim(Str::limit(preg_replace('/\s+/', ' ', $name), UVibeOptions::ORG_NAME_MAX, ''));
        $orgType = in_array($orgType, UVibeOptions::orgTypeLabels(), true) ? $orgType : 'Other';

        $existing = UserCustomPublisher::where('user_id', $user->id)->get()
            ->first(fn ($p) => $this->key($p->name) === $this->key($name));
        if ($existing) {
            return [$this->present($existing), false];
        }

        $created = UserCustomPublisher::create([
            'user_id' => $user->id,
            'name' => $name,
            'type' => 'organization',
            'org_type' => $orgType,
        ]);

        return [$this->present($created), true];
    }

    /** One organization in the shape both web and API return. */
    public function present(UserCustomPublisher $p): array
    {
        // Rows made before org types existed: a "group" was a club, anything else "Other".
        $type = $p->org_type ?: ($p->type === 'group' ? 'Club / Society' : 'Other');

        return [
            'id' => $p->id,
            'name' => $p->name,
            'type' => $p->type,
            'org_type' => $type,
            'emoji' => UVibeOptions::orgEmoji($type),
            'source' => 'custom',
        ];
    }

    protected function key(string $name): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($name))));
    }
}
