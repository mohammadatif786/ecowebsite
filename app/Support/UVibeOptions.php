<?php

namespace App\Support;

/**
 * Everything the "Post to U Vibe" composer lets a member choose — post types, organization types,
 * and the text-card looks — in one place. The website gets it with the U Vibe page and the mobile
 * app from `GET uvibe/options`, so both show the same choices and the server accepts only these.
 *
 * Values are the ones in the U Vibe design (UVIBE_CATS, UV_ORG_TYPES, TR_PALETTES, TR_FONTS…).
 */
final class UVibeOptions
{
    /** [id, label] — the "Type" of a post (the feed's "All" chip is not a post type). */
    public const CATEGORIES = [
        ['events', 'Events'],
        ['parties', 'Parties'],
        ['campus-life', 'Campus Life'],
        ['clubs', 'Clubs'],
        ['food', 'Food'],
        ['opportunity', 'Gigs'],
    ];

    /** [label, emoji] — what kind of organization a member posts for. */
    public const ORG_TYPES = [
        ['Student Union', '🏛️'],
        ['University (official)', '🎓'],
        ['Club / Society', '🤝'],
        ['Faculty / Department', '📚'],
        ['Event Committee', '🎉'],
        ['Sports Team', '🏅'],
        ['Other', '🏫'],
    ];

    /** [name, [gradient colours], text colour] */
    public const PALETTES = [
        ['Sunset Bay', ['#ff5f6d', '#ffc371'], '#ffffff'],
        ['Junkanoo', ['#f72585', '#7209b7', '#4361ee'], '#ffffff'],
        ['Sea Glass', ['#0cebeb', '#20e3b2', '#29ffc6'], '#06403d'],
        ['Hibiscus', ['#ff0844', '#ffb199'], '#ffffff'],
        ['Mango', ['#f9d423', '#ff4e50'], '#3b1200'],
        ['Blue Hole', ['#0f2027', '#203a43', '#2c5364'], '#e6fbff'],
        ['Flamingo', ['#ff9a9e', '#fecfef'], '#7a1140'],
        ['Reef', ['#11998e', '#38ef7d'], '#ffffff'],
        ['Carnival', ['#fc466b', '#3f5efb'], '#ffffff'],
        ['Gold Rush', ['#0b0b0b', '#3a2c00'], '#fbbf24'],
        ['Sand', ['#f5e6ca', '#e8cfa0'], '#3b2a14'],
        ['Paper', ['#ffffff', '#eef2f7'], '#0f172a'],
    ];

    /** [label, css] — `{ink}` is replaced by the palette's text colour. */
    public const FONTS = [
        ['Bold', 'font-weight:900;'],
        ['Elegant', "font-weight:700;font-style:italic;font-family:Georgia,serif;"],
        ['Typewriter', "font-weight:700;font-family:'Courier New',monospace;"],
        ['Shout', "font-weight:900;text-transform:uppercase;letter-spacing:.04em;font-family:Impact,'Arial Narrow Bold',sans-serif;"],
        ['Neon', 'font-weight:900;text-shadow:0 0 .25em {ink},0 0 .7em {ink};'],
        ['Outline', 'font-weight:900;color:transparent;-webkit-text-stroke:.04em {ink};'],
    ];

    /** [id, label] */
    public const MOTIONS = [['still', 'Still'], ['flow', 'Flow'], ['pop', 'Pop-in'], ['pulse', 'Pulse']];

    public const STICKERS = ['🔥', '❤️', '✨', '🌴', '🎉', '🌊', '😂', '🙏'];

    public const STARTERS = ['Hot take: ', 'Mood today: ', 'Unpopular opinion: ', 'Big up to ', 'Island wisdom: ', 'Nobody talks about how ', 'This or that: ', 'Reminder: '];

    /**
     * [key, label, emoji, coins] — the Boosts a member can send to a post. Coins only: a Boost
     * is appreciation, it is never cash and the poster is not paid coins for it.
     */
    public const BOOSTS = [
        ['freshman-fuel', 'Freshman Fuel', '🎒', 10],
        ['study-squad', 'Study Squad', '📚', 25],
        ['campus-cheer', 'Campus Cheer', '📣', 50],
        ['honor-roll', 'Honor Roll', '⭐', 75],
        ['deans-list', "Dean's List", '🏆', 100],
        ['class-president', 'Class President', '🎓', 150],
        ['big-boost', 'Big Boost', '🚀', 250],
        ['valedictorian', 'Valedictorian', '🥇', 400],
        ['campus-legend', 'Campus Legend', '🔥', 600],
        ['alumni-royalty', 'Alumni Royalty', '👑', 1000],
    ];

    /** [key, emoji, label, description, severity] — why a member can report a post. */
    public const REPORT_REASONS = [
        ['nudity', '🔞', 'Nudity or pornography', 'Nude, sexual or explicit photos, video, text or links', 'High'],
        ['hate', '🛑', 'Hate speech or discrimination', 'Attacks on race, nationality, religion, gender, sexuality or disability', 'High'],
        ['harassment', '😡', 'Harassment or bullying', 'Targeting, shaming or intimidating a person', 'High'],
        ['defamation', '🤥', 'Lies or defamation about someone', 'False statements meant to damage a person’s name', 'High'],
        ['violence', '👊', 'Violence or fighting', 'Fights, assaults or other violent recordings', 'High'],
        ['threats', '⚠️', 'Threats or intent to harm', 'Threatening or trying to hurt or destroy someone', 'High'],
        ['privacy', '🕵️', 'Private information or impersonation', 'Someone’s address, phone, photos or identity used without consent', 'High'],
        ['illegal', '💊', 'Illegal activity', 'Drugs, weapons or other unlawful activity', 'High'],
        ['selfharm', '💔', 'Self-harm or suicide', 'Content that shows or encourages self-harm', 'High'],
        ['scam', '🎣', 'Scam, fraud or spam', 'Fake offers, coin scams, misleading links', 'Medium'],
        ['other', '📝', 'Something else', 'Doesn’t bring a clean vibe — tell us why below', 'Medium'],
    ];

    /** The message "Say Thank You" starts with; the member can edit it before sending. */
    public const THANK_YOU_TEMPLATE = "Hey! I just wanted to say thank you so much for the Vibes and coins you've sent my way — it really means a lot and I truly appreciate the support! 🙏💛";

    /** The longest thank-you message. */
    public const THANK_YOU_MAX = 600;

    /** What a member can type into their own post. */
    public const ROLE_MAX = 40;
    public const ORG_NAME_MAX = 60;

    public static function categoryIds(): array
    {
        return array_column(self::CATEGORIES, 0);
    }

    public static function orgTypeLabels(): array
    {
        return array_column(self::ORG_TYPES, 0);
    }

    public static function motionIds(): array
    {
        return array_column(self::MOTIONS, 0);
    }

    /** @return array{0:string,1:string,2:string,3:int}|null [key, label, emoji, coins] */
    public static function boost(?string $key): ?array
    {
        foreach (self::BOOSTS as $b) {
            if ($b[0] === $key) {
                return $b;
            }
        }

        return null;
    }

    /** @return array{0:string,1:string,2:string,3:string,4:string}|null [key, emoji, label, description, severity] */
    public static function reportReason(?string $key): ?array
    {
        foreach (self::REPORT_REASONS as $r) {
            if ($r[0] === $key) {
                return $r;
            }
        }

        return null;
    }

    public static function reportReasonKeys(): array
    {
        return array_column(self::REPORT_REASONS, 0);
    }

    public static function orgEmoji(?string $type): string
    {
        foreach (self::ORG_TYPES as [$label, $emoji]) {
            if ($label === $type) {
                return $emoji;
            }
        }

        return '🏫';
    }

    /**
     * The look a member picked, cleaned to known values. Anything unknown falls back to the
     * default look (first palette and font, "flow", no sticker).
     *
     * @return array{pal:int, font:int, motion:string, sticker:string}
     */
    public static function normalizeStyle(?array $style): array
    {
        $pal = (int) ($style['pal'] ?? 0);
        $font = (int) ($style['font'] ?? 0);
        $motion = (string) ($style['motion'] ?? 'flow');
        $sticker = (string) ($style['sticker'] ?? '');

        return [
            'pal' => isset(self::PALETTES[$pal]) ? $pal : 0,
            'font' => isset(self::FONTS[$font]) ? $font : 0,
            'motion' => in_array($motion, self::motionIds(), true) ? $motion : 'flow',
            'sticker' => in_array($sticker, self::STICKERS, true) ? $sticker : '',
        ];
    }

    /**
     * What older renderers (the main Vibes feed, grids) draw for a text card: a background and a
     * font name. Derived on the server so web and app posts can't disagree.
     *
     * @return array{bg:string, font:string}
     */
    public static function legacyLook(array $style): array
    {
        $style = self::normalizeStyle($style);

        return [
            'bg' => 'linear-gradient(135deg,'.implode(',', self::PALETTES[$style['pal']][1]).')',
            'font' => self::FONTS[$style['font']][0],
        ];
    }

    /**
     * A text card ready to draw: words, background, text colour, font css, motion and sticker.
     * `$style` null = an older text post that only has a background.
     *
     * @return array{text:string, bg:string, ink:string, font_css:string, motion:string, sticker:string}
     */
    public static function textCard(?array $style, ?string $text, ?string $legacyBg = null): array
    {
        if (! $style) {
            return [
                'text' => (string) $text,
                'bg' => $legacyBg ?: 'linear-gradient(135deg,#a855f7,#ec4899)',
                'ink' => '#ffffff',
                'font_css' => self::FONTS[0][1],
                'motion' => 'still',
                'sticker' => '',
            ];
        }

        $style = self::normalizeStyle($style);
        [, $colours, $ink] = self::PALETTES[$style['pal']];

        return [
            'text' => (string) $text,
            'bg' => 'linear-gradient(135deg,'.implode(',', $colours).')',
            'ink' => $ink,
            'font_css' => str_replace('{ink}', $ink, self::FONTS[$style['font']][1]),
            'motion' => $style['motion'],
            'sticker' => $style['sticker'],
        ];
    }

    /** Everything the composer needs, as JSON. */
    public static function all(): array
    {
        return [
            'categories' => array_map(fn ($c) => ['id' => $c[0], 'label' => $c[1]], self::CATEGORIES),
            'org_types' => array_map(fn ($t) => ['label' => $t[0], 'emoji' => $t[1]], self::ORG_TYPES),
            'text_card' => [
                'palettes' => array_map(fn ($p) => ['name' => $p[0], 'colors' => $p[1], 'ink' => $p[2]], self::PALETTES),
                'fonts' => array_map(fn ($f) => ['label' => $f[0], 'css' => $f[1]], self::FONTS),
                'motions' => array_map(fn ($m) => ['id' => $m[0], 'label' => $m[1]], self::MOTIONS),
                'stickers' => self::STICKERS,
                'starters' => self::STARTERS,
            ],
            'boosts' => array_map(fn ($b) => ['key' => $b[0], 'label' => $b[1], 'emoji' => $b[2], 'coins' => $b[3]], self::BOOSTS),
            'report_reasons' => array_map(fn ($r) => ['key' => $r[0], 'emoji' => $r[1], 'label' => $r[2], 'description' => $r[3], 'severity' => $r[4]], self::REPORT_REASONS),
            'thank_you_template' => self::THANK_YOU_TEMPLATE,
            'limits' => [
                'thank_you_max' => self::THANK_YOU_MAX,
                'role_max' => self::ROLE_MAX,
                'org_name_max' => self::ORG_NAME_MAX,
                'max_universities' => 5,
                'report_notes_max' => 500,
                'max_photos' => (int) config('vibes.max_media', 10),
                'max_photo_kb' => (int) config('vibes.max_image_kb', 10 * 1024),
            ],
        ];
    }
}
