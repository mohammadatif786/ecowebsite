<?php

namespace App\DTOs;

use App\Models\Asue;

class SimulateAsueAcceptDTO
{
    public function __construct(
        public readonly Asue $asue,
        public readonly int $userId,
    ) {}

    public static function fromRequest(Asue $asue, int $userId): self
    {
        return new self($asue, $userId);
    }
}
