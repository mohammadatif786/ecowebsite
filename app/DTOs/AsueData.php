<?php

namespace App\DTOs;

class AsueData
{
    public function __construct(
        public readonly string $name,
        public readonly string $frequency,
        public readonly int $handAmount,
        public readonly string $startDate,
        public readonly int $maxMembers,
        public readonly array $userIds,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['name'],
            $data['frequency'],
            $data['hand_amount'],
            $data['start_date'],
            $data['max_members'],
            $data['user_ids'],
        );
    }
}