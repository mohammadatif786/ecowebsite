<?php

namespace App\Actions;

use App\DTOs\EventIndexFilters;
use App\Models\EventCategory;
use App\Models\User;
use App\Repositories\EventRepository;
use Illuminate\Support\Collection;

/**
 * Builds the full payload for the public events-index page.
 *
 * This is the ONLY place that knows "free events get excluded from
 * every other list" — that rule used to be repeated 4x in the controller.
 */
class GetEventIndexDataAction
{
    public function __construct(
        private readonly EventRepository $events,
    ) {}

    public function handle(User $user, EventIndexFilters $filters): array
    {
        $freeEvents = $this->events->getFreeEvents();
        $freeEventIds = $freeEvents->pluck('id');

        $excludeFree = fn(Collection $events): Collection => $events
            ->reject(fn($event) => $freeEventIds->contains($event->id))
            ->values();

        return [
            'events' => $excludeFree($this->events->getFilteredEvents($filters)),
            'allCategories' => EventCategory::all(),
            'newProvidence' => $excludeFree($this->events->getTrendingInSameCity()),
            'eventLowCost' => $excludeFree($this->events->getLowCostEvents()),
            'jamaicaEvent' => $excludeFree($this->events->getEventsInSameCountry($user)),
            'user' => $user,
            'freeEvents' => $freeEvents,
        ];
    }
}
