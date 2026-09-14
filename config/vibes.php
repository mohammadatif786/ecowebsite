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
];
