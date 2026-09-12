<?php

namespace App\DTOs;

class SubscriptionSuccessData
{
    public function __construct(
        public readonly string $sessionId,
        public readonly int    $userId,
        public readonly int    $planId,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            sessionId: $data['session_id'],
            userId: (int) $data['user_id'],
            planId: (int) $data['plan_id'],
        );
    }
}
