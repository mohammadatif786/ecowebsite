<?php

namespace App\Enums;

enum GateLockType: string
{
    case Free = 'free';
    case Paid = 'paid';
    case Tier = 'tier';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Paid => 'Paid',
            self::Tier => 'Tier',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Free => 'badge-free',
            self::Paid => 'badge-paid',
            self::Tier => 'badge-tier',
        };
    }
}
