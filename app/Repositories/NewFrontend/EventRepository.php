<?php

namespace App\Repositories\NewFrontend;

use App\DTOs\EventFilterCriteria;
use App\DTOs\NewFrontend\EventIndexFilters;
use App\Models\LinkUpEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EventRepository
{
    private const STANDARD_RELATIONS = ['category', 'authUserFavorite', 'eventDetails', 'tickets.drinkPackage', 'tickets.extraSetting', 'tickets.wellnessSlotBlocks', 'organizer', 'sponsors', 'coupons'];
    private const RICH_RELATIONS = ['category', 'authUserFavorite', 'favourites', 'eventDetails', 'tickets.drinkPackage', 'tickets.extraSetting', 'tickets.wellnessSlotBlocks', 'organizer', 'sponsors', 'coupons'];

    private function baseQuery(): Builder
    {
        return LinkUpEvent::with(self::STANDARD_RELATIONS)
            ->withMin(['tickets' => fn($q) => $q->availableForSale()], 'price')
            ->withMax(['tickets' => fn($q) => $q->availableForSale()], 'price')
            ->withCount(['tickets' => fn($q) => $q->availableForSale()])
            ->withCount(['event_audience'])
            ->with(['organizer' => function($q) {
                $q->withCount('followers');
                if (auth()->check()) {
                    $q->withExists(['followers as is_following' => fn($query) => $query->where('user_id', auth()->id())]);
                }
            }]);
    }

    public function getFilteredEvents(EventIndexFilters $filters): Collection
    {
        $query = $this->baseQuery()
            ->where('status', 'live')
            ->activeTodayAndFuture(); // Uses eventDetails table for proper date filtering

        $this->applySearchFilter($query, $filters->search);
        $this->applyCategoryFilter($query, $filters->category_id);
        $this->applyLocationFilter($query, $filters);

        return $query->orderByDesc('id')->get();
    }

    private function applyLocationFilter(Builder $query, EventIndexFilters $filters): void
    {
        if ($filters->lat !== null && $filters->lng !== null) {
            // Using correct column names from schema: 'latitude' and 'longtitude'
            $query->whereBetween('latitude', [$filters->lat - 0.5, $filters->lat + 0.5])
                  ->whereBetween('longtitude', [$filters->lng - 0.5, $filters->lng + 0.5]);
            return;
        }

        if (filled($filters->city) && $filters->city !== 'Current Location') {
            $query->where('city', 'like', '%' . $this->escapeLike($filters->city) . '%');
        }
    }

    public function getTrendingInSameCity(?EventIndexFilters $filters = null, int $limit = 10): Collection
    {
        $query = $this->baseQuery()
            ->whereHas('favourites')
            ->activeTodayAndFuture();

        if ($filters) {
            $this->applyLocationFilter($query, $filters);
        }

        return $query->withCount('favourites as total_favourite')
            ->orderByDesc('total_favourite')
            ->take($limit)
            ->get();
    }

    public function getEventsInSameCountry(User $user, ?EventIndexFilters $filters = null, int $limit = 10): Collection
    {
        $query = $this->baseQuery();

        if ($filters && ($filters->lat !== null || filled($filters->city))) {
             $this->applyLocationFilter($query, $filters);
        } else {
             $query->when($user->country, fn(Builder $q) => $q->where('country', $user->country));
        }

        return $query->activeTodayAndFuture()
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getLowCostEvents(float $maxPrice = 30, ?EventIndexFilters $filters = null, int $limit = 10): Collection
    {
        $query = $this->baseQuery()
            ->whereHas('tickets', fn(Builder $q) => $q->where('price', '<=', $maxPrice))
            ->activeTodayAndFuture();

        if ($filters) {
            $this->applyLocationFilter($query, $filters);
        }

        return $query->latest()
            ->take($limit)
            ->get();
    }

    public function getFreeEvents(?EventIndexFilters $filters = null, int $limit = 10): Collection
    {
        $query = LinkUpEvent::with(['category', 'authUserFavorite', 'favourites', 'eventDetails', 'tickets.drinkPackage', 'tickets.extraSetting', 'tickets.wellnessSlotBlocks', 'organizer'])
            ->withMin(['tickets' => fn($q) => $q->availableForSale()], 'price')
            ->withMax(['tickets' => fn($q) => $q->availableForSale()], 'price')
            ->withCount(['tickets' => fn($q) => $q->availableForSale()])
            ->withCount(['event_audience'])
            ->with(['organizer' => function($q) {
                $q->withCount('followers');
                if (auth()->check()) {
                    $q->withExists(['followers as is_following' => fn($query) => $query->where('user_id', auth()->id())]);
                }
            }])
            ->where('status', 'live')
            ->activeTodayAndFuture();

        if ($filters) {
            $this->applyLocationFilter($query, $filters);
        }

        return $query->whereHas('tickets', fn($q) => $q->availableForSale())
            ->whereDoesntHave('tickets', function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('price', '>', 0)->orWhere('table_price', '>', 0);
                });
            })
            ->latest()
            ->get()
            ->filter(function (LinkUpEvent $event) {
                // Double check with model logic
                return $event->tickets->filter(fn($t) => $t->quantity > 0)->isNotEmpty()
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
            'organizer' => function($q) {
                $q->withCount('followers');
                if (auth()->check()) {
                    $q->withExists(['followers as is_following' => fn($query) => $query->where('user_id', auth()->id())]);
                }
            },
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

    private function applyCategoryFilter(Builder $query, ?string $categoryId): void
    {
        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', (int)$categoryId);
        }
    }

    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (filled($search)) {
            $query->where('title', 'like', '%' . $this->escapeLike($search) . '%');
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
