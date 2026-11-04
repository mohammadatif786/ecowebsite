<?php

namespace App\DTOs\User\Signup;

/**
 * The choices the wizard offers, in one place for the server rules, the website and (later) the
 * mobile app. The website adds the pictures (icons, flags); the values themselves live here.
 */
final class WizardOptions
{
    /** [value, name in that language] */
    public const LANGUAGES = [
        ['English', 'English'],
        ['Spanish', 'Español'],
        ['French', 'Français'],
        ['Haitian Creole', 'Kreyòl Ayisyen'],
        ['Dutch', 'Nederlands'],
        ['Papiamento', 'Papiamentu'],
        ['Portuguese', 'Português'],
        ['Sranan Tongo', 'Sranantongo'],
    ];

    public const INTERESTS = [
        'Music Festivals', 'Dance Events', 'Cultural Gatherings', 'Travel', 'Cooking',
        'Books', 'Yoga', 'Movies', 'Liming', 'Wine',
    ];

    /** Label shown => value saved in users.gender */
    public const GENDERS = ['Male' => 'male', 'Female' => 'female'];

    /** Label shown => value saved in users.link_me_with (the old wizard's values) */
    public const SHOW_ME = ['Men' => 'male', 'Women' => 'female', 'Everyone' => 'both'];

    /** Groups of [id, title, description]. The titles are saved in users.whyare. */
    public const INTENTS = [
        'Meet people' => [
            ['linkup', 'Here to link up', 'Someone nice. Nothing serious.'],
            ['serious', 'Something serious', 'Looking for a real relationship.'],
            ['friends', 'Make friends', 'Meet people from the region.'],
            ['chat', 'Casual chat', 'Good conversation, nothing serious.'],
        ],
        'Explore LinkUp' => [
            ['party', 'Party & events', 'Find the next good fête and grab tickets.'],
            ['eats', 'Eats', 'Find good Caribbean and Latin food near me.'],
            ['vibes', 'Vibes', 'Post, scroll and share with the community.'],
            ['uvibes', 'UVibes', 'Link up with my campus and alumni crew.'],
            ['live', 'Live feed', 'Watch what’s happening, or go LIVE yourself.'],
            ['coins', 'Earn LinkUp Coins', 'Go LIVE, look cute and collect coins.'],
            ['market', 'Marketplace', 'Shop from people and businesses in the community.'],
            ['sell', 'Sell or promote', 'List products, services or my own events.'],
        ],
    ];

    public const CARIBBEAN = [
        'Anguilla', 'Antigua and Barbuda', 'Aruba', 'Bahamas', 'Barbados', 'Belize', 'British Virgin Islands',
        'Cayman Islands', 'Cuba', 'Curaçao', 'Dominica', 'Dominican Republic', 'Grenada', 'Guadeloupe',
        'Guyana', 'Haiti', 'Jamaica', 'Martinique', 'Montserrat', 'Puerto Rico', 'Saint Kitts and Nevis',
        'Saint Lucia', 'Saint Vincent and the Grenadines', 'Sint Maarten', 'Suriname', 'Trinidad and Tobago',
        'Turks and Caicos Islands', 'U.S. Virgin Islands',
    ];

    public const LATIN_AMERICA = [
        'Argentina', 'Bolivia', 'Brazil', 'Chile', 'Colombia', 'Costa Rica', 'Ecuador', 'El Salvador',
        'Guatemala', 'Honduras', 'Mexico', 'Nicaragua', 'Panama', 'Paraguay', 'Peru', 'Uruguay', 'Venezuela',
    ];

    public const WORK_FIELDS = [
        'Healthcare', 'Tech', 'Creative & design', 'Music & entertainment', 'Education', 'Business owner',
        'Hospitality & tourism', 'Finance', 'Student', 'Trades',
    ];

    /** Distance slider: 1-160 km, where 160 means "Anywhere". */
    public const DISTANCE_MIN_KM = 1;
    public const DISTANCE_ANYWHERE_KM = 160;

    /** Saved as the upper distance for "Anywhere" (more than half the Earth's circumference). */
    public const DISTANCE_ANYWHERE_SAVED_KM = 20000;

    /** Age slider: 18-80, where 80 means "80+". */
    public const AGE_MIN = 18;
    public const AGE_MAX = 80;

    /** Saved as the upper age for "80+". */
    public const AGE_NO_LIMIT = 120;

    public const MAX_PHOTOS = 6;

    /** @return list<string> */
    public static function languageValues(): array
    {
        return array_column(self::LANGUAGES, 0);
    }

    /** @return array<string, string> id => title */
    public static function intentTitles(): array
    {
        $titles = [];
        foreach (self::INTENTS as $items) {
            foreach ($items as [$id, $title]) {
                $titles[$id] = $title;
            }
        }

        return $titles;
    }

    /** @return list<string> */
    public static function countries(): array
    {
        return array_merge(self::CARIBBEAN, self::LATIN_AMERICA);
    }

    /** Everything the wizard page needs, in a JSON-friendly shape. */
    public static function toArray(): array
    {
        return [
            'languages' => array_map(fn ($l) => ['name' => $l[0], 'native' => $l[1]], self::LANGUAGES),
            'interests' => self::INTERESTS,
            'genders' => array_keys(self::GENDERS),
            'showMe' => array_keys(self::SHOW_ME),
            'intents' => array_map(
                fn ($title, $items) => [
                    'title' => $title,
                    'items' => array_map(fn ($i) => ['id' => $i[0], 'title' => $i[1], 'desc' => $i[2]], $items),
                ],
                array_keys(self::INTENTS),
                self::INTENTS,
            ),
            'caribbean' => self::CARIBBEAN,
            'latinAmerica' => self::LATIN_AMERICA,
            'workFields' => self::WORK_FIELDS,
            'maxPhotos' => self::MAX_PHOTOS,
        ];
    }
}
