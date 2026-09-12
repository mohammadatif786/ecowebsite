<?php

namespace App\DTOs;

use App\Models\LinkUpEvent;

final class EventTypeFlags
{
    public function __construct(
        public readonly bool $isWellnessEvent = false,
        public readonly bool $isCookoutEvent = false,
    ) {
    }

    public static function fromEvent(LinkUpEvent $event): self
    {
        $eventType = $event->eventDetails?->event_type ?? null;

        if (! $eventType) {
            return new self();
        }

        $normalized = strtolower(trim($eventType));

        return new self(
            isWellnessEvent: str_contains($normalized, 'wellness') || str_contains($normalized, 'spa'),
            isCookoutEvent: str_contains($normalized, 'cookout'),
        );
    }
}
