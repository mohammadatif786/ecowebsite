<?php

namespace App\Enums;

enum SubscriptionPlanStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
}
