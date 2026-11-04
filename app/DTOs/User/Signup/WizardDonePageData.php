<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * Props for the "You're all linked up" screen (User/Signup/WizardDone): where to start, based on
 * what the member said brought them to LinkUp.
 */
final readonly class WizardDonePageData
{
    /** intent id => [destination title, description, icon key, route name] */
    private const DESTINATIONS = [
        'linkup' => ['Discover', 'See people near you', 'smile', 'user.linkup'],
        'serious' => ['Discover', 'See people near you', 'heart', 'user.linkup'],
        'friends' => ['Discover', 'See people near you', 'people', 'user.linkup'],
        'chat' => ['Messages', 'Start a conversation', 'chat', 'user.dating.chats'],
        'party' => ['Events', 'Browse fêtes and tickets', 'ticket', 'user.events'],
        'eats' => ['Eats', 'Find food near you', 'eats', 'user.eats'],
        'vibes' => ['Vibes', 'See what the community is posting', 'spark', 'user.vibes'],
        'uvibes' => ['UVibes', 'Meet your campus crew', 'cap', 'user.uvibe'],
        'live' => ['Live feed', 'Watch or go LIVE', 'live', 'user.live'],
        'coins' => ['Go LIVE', 'Start earning LinkUp Coins', 'coin', 'user.live'],
        'market' => ['Marketplace', 'Shop the community', 'shop', 'user.marketplace'],
        'sell' => ['Sell', 'Create your first listing', 'tag', 'user.marketplace'],
    ];

    /** Shown when the member picked nothing (the design's fallback). */
    private const FALLBACK = ['party', 'linkup', 'live', 'market'];

    /**
     * @param  list<string>  $intents
     */
    public function __construct(
        public User $user,
        public array $intents,
    ) {}

    public function toArray(): array
    {
        $ids = array_values(array_filter($this->intents, fn ($id) => isset(self::DESTINATIONS[$id])));
        if ($ids === []) {
            $ids = self::FALLBACK;
        }

        $seen = [];
        $destinations = [];
        foreach ($ids as $id) {
            [$title, $desc, $icon, $route] = self::DESTINATIONS[$id];
            if (isset($seen[$title])) {
                continue;
            }
            $seen[$title] = true;
            $destinations[] = ['title' => $title, 'desc' => $desc, 'icon' => $icon, 'url' => route($route)];
        }

        return [
            'firstName' => (string) $this->user->first_name,
            'destinations' => $destinations,
            'urls' => [
                'explore' => route('user.feed'),
                'profile' => route('user.profile'),
            ],
        ];
    }
}
