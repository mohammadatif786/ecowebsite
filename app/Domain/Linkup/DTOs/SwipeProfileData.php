<?php

namespace App\Domain\Linkup\DTOs;

readonly class SwipeProfileData
{
    public function __construct(public int $targetId, public string $action) {}

    public static function fromValidated(array $data): self
    {
        return new self((int) $data['target_id'], $data['action'] === 'pass' ? 'dislike' : $data['action']);
    }
}
