<?php

namespace App\Domain\Vibes\Enums;

enum VibeMediaSource: string
{
    case Camera = 'camera';
    case VideoRecording = 'video_recording';
    case Gallery = 'gallery';
}
