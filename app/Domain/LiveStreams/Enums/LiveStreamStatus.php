<?php

namespace App\Domain\LiveStreams\Enums;

enum LiveStreamStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

    public static function canTransition(string $from, self $to): bool
    {
        return match ($from) {
            self::Draft->value, 'created' => in_array($to, [self::Scheduled, self::Live, self::Cancelled], true),
            self::Scheduled->value => in_array($to, [self::Live, self::Cancelled], true),
            self::Live->value => $to === self::Ended,
            default => false,
        };
    }
}
