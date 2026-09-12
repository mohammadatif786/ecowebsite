<?php

namespace App\Actions;

use App\DTOs\EventTypeFlags;
use App\Models\LinkUpEvent;
use App\Repositories\EventRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\SponsorRepository;
use App\Repositories\TicketRepository;
use App\Services\CurrencyResolver;
use App\Actions\BuildOrganizerStatsAction;
use App\Actions\BuildAttendeeListAction;

class ShowEventAction
{
    public function __construct(
        private readonly EventRepository $events,
        private readonly TicketRepository $tickets,
        private readonly SponsorRepository $sponsors,
        private readonly SettingsRepository $settings,
        private readonly BuildAttendeeListAction $buildAttendeeList,
        private readonly BuildOrganizerStatsAction $buildOrganizerStats,
        private readonly CurrencyResolver $currency,
    ) {}

    public function handle(LinkUpEvent $event): array
    {
        $this->events->loadShowRelations($event);

        $organizerStats = $this->buildOrganizerStats->handle($event);
        $typeFlags = EventTypeFlags::fromEvent($event);

        return [
            'otherEvents' => $this->events->getOtherEventsByOrganizer($event),
            'event' => $event,
            // config('app.url'), not env('APP_URL') — env() returns null in
            // production once you run `php artisan config:cache`, since
            // cached config no longer reads the .env file at all.
            'appurl' => config('app.url') . '/storage/',
            'sponsor' => $this->sponsors->getForEvent($event),
            'organizerStats' => $organizerStats->stats,
            'tickets' => $this->tickets->getActiveTicketsForEvent($event),
            'attendees' => $this->buildAttendeeList->handle($event),
            'eventFeeSettings' => $this->settings->getFeeSettingsForEvent($event),
            // Verify 'longtitude' is the real column name and not a typo —
            // left exactly as the original to avoid silently breaking a
            // column reference I can't confirm against your schema.
            'show_map' => (bool) ($event->latitude && $event->longtitude),
            'taxRules' => $this->settings->getTaxRules(),
            'currency' => $this->currency->resolve($organizerStats->currencyCode),
            'isWellnessEvent' => $typeFlags->isWellnessEvent,
            'isCookoutEvent' => $typeFlags->isCookoutEvent,
        ];
    }
}
