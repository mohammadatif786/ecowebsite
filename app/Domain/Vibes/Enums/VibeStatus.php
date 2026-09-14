<?php

namespace App\Domain\Vibes\Enums;

enum VibeStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Published = 'published';
    case Failed = 'failed';
    case Archived = 'archived';
}
