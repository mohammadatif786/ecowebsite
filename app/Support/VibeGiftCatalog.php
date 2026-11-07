<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * The gifts members can send on Vibes and reels (Big Ups) and in "Send a Vibe".
 * The server decides each gift's name, emoji and price — the app only says which gift (`gift_id`).
 * Older apps that still send `gift_name` get the catalog gift with that name, at the catalog price.
 * A Big Up with no gift at all is a plain "Big Up ⚡" for the coins the member chose.
 */
final class VibeGiftCatalog
{
    /** id => [name, emoji, coins] */
    public const GIFTS = [
        // Big Ups (posts and reels)
        'likkle' => ['Likkle Up', '🤙', 10],
        'bigup' => ['Big Up', '🙌', 25],
        'respect' => ['Respect', '👊', 50],
        'bless' => ['Bless Up', '🙏', 75],
        'nuff' => ['Nuff Respect', '💯', 100],
        'blessings' => ['Blessings', '🕊️', 150],
        'one_love' => ['One Love', '❤️', 200],
        'god_bless' => ['God Bless You', '😇', 250],
        'love_respect' => ['Love & Respect', '💕', 350],
        'big_tings' => ['Big Tings', '🔥', 500],
        'crown' => ['Caribbean Crown', '👑', 1000],
        // Send a Vibe (to a member)
        'smile' => ['Likkle Smile', '😊', 10],
        'soca' => ['Soca Vibe', '🎶', 35],
        'wine' => ['Wine & Dance', '💃', 50],
        'sunset' => ['Sunset Lime', '🌅', 75],
        'coconut' => ['Coconut Cheers', '🥥', 90],
        'rose' => ['Island Rose', '🌹', 120],
        'kiss' => ['Sweet Kiss', '😘', 150],
        'candle' => ['Candlelit Date', '🕯️', 220],
        'beach' => ['Beach Linkup', '🏝️', 250],
        'sweetheart' => ['Sweetheart', '💖', 300],
        'royalty' => ['Island Royalty', '👑', 400],
    ];

    public const CUSTOM = ['Big Up', '⚡'];

    /**
     * @param  array{gift_id?: ?string, gift_name?: ?string, coins?: mixed}  $input
     * @param  bool  $allowCustom  A plain Big Up for any amount when no gift is named.
     * @return array{id: ?string, name: string, emoji: string, coins: int}
     *
     * @throws ValidationException
     */
    public static function resolve(array $input, bool $allowCustom = true): array
    {
        $id = $input['gift_id'] ?? null;

        if ($id === null && filled($input['gift_name'] ?? null)) {
            $id = collect(self::GIFTS)->search(fn ($g) => mb_strtolower($g[0]) === mb_strtolower(trim($input['gift_name'])));
            if ($id === false) {
                throw ValidationException::withMessages(['gift_id' => 'Choose a gift from the list.']);
            }
        }

        if ($id !== null) {
            if (! isset(self::GIFTS[$id])) {
                throw ValidationException::withMessages(['gift_id' => 'Choose a gift from the list.']);
            }
            [$name, $emoji, $coins] = self::GIFTS[$id];

            return ['id' => $id, 'name' => $name, 'emoji' => $emoji, 'coins' => $coins];
        }

        $coins = (int) ($input['coins'] ?? 0);
        if (! $allowCustom || $coins < 1) {
            throw ValidationException::withMessages(['gift_id' => 'Choose a gift to send.']);
        }

        return ['id' => null, 'name' => self::CUSTOM[0], 'emoji' => self::CUSTOM[1], 'coins' => $coins];
    }

    /** Validation rules shared by every gift endpoint. */
    public static function rules(): array
    {
        return [
            'gift_id' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::GIFTS))],
            'gift_name' => ['nullable', 'string', 'max:60'],
            'coins' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
