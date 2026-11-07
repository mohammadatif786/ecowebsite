<?php

return [
    'disk' => env('VIBE_MEDIA_DISK', 'public'),
    'max_media' => 10,
    'max_image_kb' => 10 * 1024,
    'max_video_kb' => 100 * 1024,
    'max_total_kb' => 150 * 1024,
    'max_attachments_per_type' => 10,
    'caption_max' => 2200,
    'image_mimetypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    'video_mimetypes' => ['video/mp4', 'video/quicktime', 'video/webm'],

    // Days a commission from a Vibe sale stays "pending" before the creator can
    // release it to "available" (time for refunds/disputes). Marketplace order
    // commissions are released when the buyer confirms delivery instead.
    'affiliate_hold_days' => (int) env('VIBE_AFFILIATE_HOLD_DAYS', 7),
    // Text-post backgrounds: a colour or a simple gradient of colours, nothing else (no url(), no CSS tricks).
    'text_bg_pattern' => '/^(#[0-9a-fA-F]{3,8}|linear-gradient\(\d{1,3}deg(,\s*#[0-9a-fA-F]{3,8}(\s+\d{1,3}%)?){2,5}\))$/',
    'text_font_pattern' => '/^[\pL\pN ._\-]{1,100}$/u',
    // Converting received gift coins to wallet cash (Gifts tab). Never taken from the request.
    'cashout' => [
        'coin_rate' => 0.01,      // USD per coin
        'payout_ratio' => 0.5,    // the member's share; the rest is the platform's
        'minimum_usd' => (float) env('VIBES_CASHOUT_MINIMUM', 25),
    ],
];
