<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active    = 'active';
    case Trialing  = 'trialing';
    case PastDue   = 'past_due';
    case Cancelled = 'canceled';
    case Incomplete = 'incomplete';

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Trialing  => 'Trialing',
            self::PastDue   => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Incomplete => 'Incomplete',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Trialing]);
    }
}
