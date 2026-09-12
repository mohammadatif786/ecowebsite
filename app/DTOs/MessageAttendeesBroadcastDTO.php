<?php

namespace App\DTOs;

class MessageAttendeesBroadcastDTO
{
    public function __construct(
        public int $eventId,
        public ?string $audience,
        public ?string $preset,
        public string $subject,
        public string $body,
        public bool $chLinkUp,
        public bool $chEmail,
        public bool $chSMS,
        public int $totalSent,
        public int $totalDelivered,
        public int $totalFailed,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            audience: $data['audience'] ?? null,
            preset: $data['preset'] ?? null,
            subject: $data['subject'],
            body: $data['body'],
            chLinkUp: (bool) ($data['chLinkUp'] ?? false),
            chEmail: (bool) ($data['chEmail'] ?? false),
            chSMS: (bool) ($data['chSMS'] ?? false),
            totalSent: $data['totalSent'] ?? 0,
            totalDelivered: $data['totalDelivered'] ?? 0,
            totalFailed: $data['totalFailed'] ?? 0,
        );
    }
}
