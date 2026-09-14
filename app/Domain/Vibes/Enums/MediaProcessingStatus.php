<?php

namespace App\Domain\Vibes\Enums;

enum MediaProcessingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
