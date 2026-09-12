<?php

namespace App\Actions;

use App\DTOs\EventFilterCriteria;
use App\Models\EventCategory;
use App\Repositories\EventRepository;

class FilterEventsAction
{
    public function __construct(
        private readonly EventRepository $events,
    ) {}

    public function handle(EventFilterCriteria $criteria): array
    {
        return [
            'events' => $this->events->filterEvents($criteria),
            'allCategories' => EventCategory::get(),
        ];
    }
}
