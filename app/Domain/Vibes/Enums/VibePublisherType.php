<?php

namespace App\Domain\Vibes\Enums;

enum VibePublisherType: string
{
    case User = 'user';
    case Organization = 'organization';
    case Group = 'group';
}
