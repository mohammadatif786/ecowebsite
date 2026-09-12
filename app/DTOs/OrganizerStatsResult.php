<?php

namespace App\DTOs;

final class OrganizerStatsResult
{
    public function __construct(
        public readonly array $stats,
        public readonly ?string $currencyCode,
    ) {
    }
}
