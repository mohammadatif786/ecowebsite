<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case OneTime = 'one_time';
    case Lifetime = 'lifetime';

    public function stripeInterval(): ?string
    {
        return match ($this) {
            self::Monthly => 'month',
            self::Yearly => 'year',
            self::OneTime, self::Lifetime => null, // one-off Stripe price, no recurring interval
        };
    }
}
