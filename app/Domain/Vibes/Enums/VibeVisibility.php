<?php

namespace App\Domain\Vibes\Enums;

enum VibeVisibility: string
{
    case Public = 'public';
    case Friends = 'friends';
    case Followers = 'followers';
    case Private = 'private';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
