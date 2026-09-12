<?php

namespace App\Repositories;

use App\DTOs\EventFilterCriteria;
use App\DTOs\EventIndexFilters;
use App\Models\LinkUpEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EventRepository
{
    private const STANDARD_RELATIONS = ['category', 'authUserFavorite', 'eventDetails'];
    private const RICH_RELATIONS = ['category', 'authUserFavorite', 'favourites', 'eventDetails'];

    public function getFilteredEvents(EventIndexFilters $filters): Collection
    {
        $query = LinkUpEvent::with(self::STANDARD_RELATIONS)
            ->where('status', 'live')
            ->active();

        $this->applyStatusFilter($query, $filters->status);
        $this->applySearchFilter($query, $filters->search);
        $this->applyCountryFilter($query, $filters->country);

        return $query->orderByDesc('id')->get();
    }

    public function getTrendingInSameCity(int $limit = 10): Collection
    {
        return LinkUpEvent::with(self::RICH_RELATIONS)
            ->whereHas('favourites')
            ->active()
            ->withCount('favourites as total_favourite')
            ->orderByDesc('total_favourite')
            ->take($limit)
            ->get();
    }

    public function getEventsInSameCountry(User $user, int $limit = 10): Collection
    {
        return LinkUpEvent::with(self::RICH_RELATIONS)
            ->when($user->country, fn(Builder $q) => $q->where('country', $user->country))
            ->active()
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getLowCostEvents(float $maxPrice = 30, int $limit = 10): Collection
    {
        return LinkUpEvent::with(self::RICH_RELATIONS)
            ->whereHas('tickets', fn(Builder $q) => $q->where('price', '<=', $maxPrice))
            ->active()
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getFreeEvents(int $limit = 10): Collection
    {
        return LinkUpEvent::with(['category', 'authUserFavorite', 'favourites', 'eventDetails', 'tickets'])
            ->where('status', 'live')
            ->active()
            ->whereHas('tickets')
            ->whereDoesntHave('tickets', function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('price', '>', 0)->orWhere('table_price', '>', 0);
                });
            })
            ->latest()
            ->get()
            ->filter(function (LinkUpEvent $event) {
                return $event->tickets->isNotEmpty()
                    && $event->tickets->every(
                        fn($ticket) => method_exists($ticket, 'isCompletelyFree') && $ticket->isCompletelyFree()
                    );
            })
            ->take($limit)
            ->values()
            ->each(fn(LinkUpEvent $event) => $event->setRelation('tickets', collect()));
    }

    public function getOtherEventsByOrganizer(LinkUpEvent $event, int $limit = 4): Collection
    {
        return LinkUpEvent::with(self::RICH_RELATIONS)
            ->where('organizer_id', $event->organizer_id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function loadShowRelations(LinkUpEvent $event): LinkUpEvent
    {
        return $event->load([
            'category',
            'authUserFavorite',
            'favourites',
            'eventDetails',
            'coupons',
            'sponsors',
            'user',
            'owner',
            'organizer',
            'organizer.user',
            'organizer.media',
            'event_audience.user',
            'approvedReviews',
        ]);
    }

    public function filterEvents(EventFilterCriteria $criteria): Collection
    {
        $query = LinkUpEvent::with(['category', 'authUserFavorite', 'favourites', 'eventDetails']);

        $this->applyStatusFilter($query, $criteria->status);
        $this->applySearchFilter($query, $criteria->search);
        $this->applyCountryFilter($query, $criteria->country);
        $this->applyCategoryFilter($query, $criteria->categoryId);

        return $query->get();
    }

    private function applyCategoryFilter(Builder $query, ?int $categoryId): void
    {
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }
    }
    private function applyStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'upcomming' => $query->where('start_time', '>', now()),
            'live' => $query->where('start_time', '<=', now())->where('end_time', '>=', now()),
            'completed' => $query->where('end_time', '<', now()),
            default => null,
        };
    }

    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (filled($search)) {
            $query->where('title', 'like', '%' . $this->escapeLike($search) . '%');
        }
    }

    private function applyCountryFilter(Builder $query, ?string $country): void
    {
        if (filled($country) && $country !== 'all') {
            $query->where('country', 'like', '%' . $this->escapeLike($country) . '%');
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
