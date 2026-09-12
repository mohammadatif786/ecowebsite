<?php

namespace App\Services;

use App\DTOs\MessageAttendeesBroadcastDTO;
use App\Jobs\SendMessageToAttendeeJob;
use App\Models\LinkUpEvent;
use App\Models\MessageAttendeesBroad;
use App\Models\TicketSale;
use Carbon\Carbon;

class MessageAttendeesBroadcastService
{
    // return response
    public function messageAttendeesBroadcast(MessageAttendeesBroadcastDTO $dto): MessageAttendeesBroadcastDTO
    {
        $event = LinkUpEvent::query()->find($dto->eventId);
        $organizerId = (int) ($event?->user_id ?? $event?->organizer_id ?? 0);

        [$subject, $body] = $this->resolveMessageTemplate(
            subject: $dto->subject,
            body: $dto->body,
            event: $event
        );

        $resolvedDto = new MessageAttendeesBroadcastDTO(
            $dto->eventId,
            $dto->audience,
            $dto->preset,
            $subject,
            $body,
            $dto->chLinkUp,
            $dto->chEmail,
            $dto->chSMS,
            0,
            0,
            0
        );

        $this->storeBroadcast($resolvedDto);

        $attendees = $this->checkAttendeeStatus($resolvedDto, $organizerId);

        return new MessageAttendeesBroadcastDTO(
            $resolvedDto->eventId,
            $resolvedDto->audience,
            $resolvedDto->preset,
            $resolvedDto->subject,
            $resolvedDto->body,
            $resolvedDto->chLinkUp,
            $resolvedDto->chEmail,
            $resolvedDto->chSMS,
            $attendees['totalSent'],
            $attendees['totalDelivered'],
            $attendees['totalFailed']
        );
    }

    private function resolveMessageTemplate(string $subject, string $body, ?LinkUpEvent $event): array
    {
        $eventTitle = (string) ($event?->title ?? '');
        $venueFromEvent = trim((string) ($event?->venue ?? ''));
        $venueFromLocation = trim((string) ($event?->location ?? ''));
        $venueParts = array_values(array_filter([
            $venueFromLocation,
            $venueFromEvent,
            trim((string) ($event?->city ?? '')),
            trim((string) ($event?->state ?? '')),
            trim((string) ($event?->country ?? '')),
        ], fn ($v) => $v !== ''));

        $venue = count($venueParts) ? implode(', ', $venueParts) : 'TBA';
        $now = Carbon::now()->format('Y-m-d H:i');

        $replacements = [
            '{event}' => $eventTitle !== '' ? $eventTitle : '{event}',
            '{venue}' => $venue,
            '{date}' => $now,
        ];

        $resolvedSubject = strtr($subject, $replacements);
        $resolvedBody = strtr($body, $replacements);

        // If the organizer didn't include tags, append useful context automatically
        if ($eventTitle !== '' && !str_contains($body, '{event}') && !str_contains($resolvedBody, $eventTitle)) {
            $resolvedBody .= (str_ends_with($resolvedBody, "\n") ? '' : "\n") . "\nEvent: {$eventTitle}";
        }

        if (!str_contains($body, '{date}') && !str_contains($resolvedBody, $now)) {
            $resolvedBody .= (str_ends_with($resolvedBody, "\n") ? '' : "\n") . "\nDate/Time: {$now}";
        }

        return [$resolvedSubject, $resolvedBody];
    }

    // check attendee status like user status is 1 or 0
    public function checkAttendeeStatus(MessageAttendeesBroadcastDTO $dto, int $organizerId)
    {

        $attendees = TicketSale::with('user')
            ->where('link_up_event_id', $dto->eventId)
            ->where('ticket_status', 'confirmed')
            ->get();

        $totalSent = 0;
        $totalDelivered = 0;
        $totalFailed = 0;

        $uniqueUsers = [];

        foreach ($attendees as $attendee) {
            $userId = $attendee->user_id;

            if (isset($uniqueUsers[$userId])) continue;

            $uniqueUsers[$userId] = true;


            if ($attendee->user && $attendee->user->status == 1) {

                $getDataByType = $this->getAttendeesByType($dto);

                if ($getDataByType->contains('user_id', $userId)) {
                    $totalSent++;
                    $totalDelivered++;

                    SendMessageToAttendeeJob::dispatch($attendee, $dto->subject, $dto->body, [
                        'chLinkUp' => $dto->chLinkUp,
                        'chEmail'  => $dto->chEmail,
                        'chSMS'    => $dto->chSMS,
                    ], $organizerId);
                }
            } else {
                $totalFailed++;
            }
        }
        return [
            'totalSent' => $totalSent,
            'totalDelivered' => $totalDelivered,
            'totalFailed' => $totalFailed
        ];
    }

    // get attendees by type like all, checkedin, notchecked, vip, general
    public function getAttendeesByType(MessageAttendeesBroadcastDTO $dto)
    {
        $query = TicketSale::with('user', 'checkins')
            ->where('link_up_event_id', $dto->eventId)
            ->where('ticket_status', 'confirmed')
            ->whereHas('user', function ($q) {
                $q->where('status', 1);
            });

        switch ($dto->audience) {
            case 'all':
                break;

            case 'checkedin':
                $query->whereHas('checkins');
                break;

            case 'notchecked':
                $query->whereDoesntHave('checkins');
                break;

            case 'vip':
                $query->where('ticket_type', 'vip');
                break;

            case 'general':
                $query->where('ticket_type', 'general_admission');
                break;
        }

        return $query->get();
    }

    /**
     * Store message broadcast data in database
     */
    public function storeBroadcast(MessageAttendeesBroadcastDTO $dto): MessageAttendeesBroad
    {
        return MessageAttendeesBroad::create([
            'event_id' => $dto->eventId,
            'audience' => $dto->audience,
            'preset' => $dto->preset,
            'subject' => $dto->subject,
            'body' => $dto->body,
            'chLinkUp' => $dto->chLinkUp,
            'chEmail' => $dto->chEmail,
            'chSMS' => $dto->chSMS,
        ]);
    }
}
