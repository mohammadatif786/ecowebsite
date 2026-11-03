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
];
