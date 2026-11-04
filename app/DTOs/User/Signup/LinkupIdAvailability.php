<?php

namespace App\DTOs\User\Signup;

/**
 * Answer to "can I use this LinkUp ID?".
 */
final readonly class LinkupIdAvailability
{
    public function __construct(
        public bool $available,
        public ?string $reason = null,
    ) {}

    /**
     * @param  array{available: bool, reason?: string|null}  $result  shape returned by RegisteredUserAction::validateLinkUpId
     */
    public static function fromLegacy(array $result): self
    {
        return new self((bool) $result['available'], $result['reason'] ?? null);
    }

    /** @return array{available: bool, reason: string|null} */
    public function toArray(): array
    {
        return ['available' => $this->available, 'reason' => $this->reason];
    }
}
