<?php

namespace App\DTOs\NewFrontend;

use App\Http\Requests\NewFrontend\EventIndexRequest;

/**
 * Typed, immutable representation of the events-index filters.
 *
 * Built ONLY from a validated request, so by the time this object
 * exists, every property is already safe to use in a query.
 */
final class EventIndexFilters
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $category_id = null,
        public readonly ?float $lat = null,
        public readonly ?float $lng = null,
        public readonly ?string $city = null,
    ) {}

    public static function fromRequest(EventIndexRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            search: $validated['search'] ?? null,
            category_id: $validated['category_id'] ?? null,
            lat: isset($validated['lat']) ? (float)$validated['lat'] : null,
            lng: isset($validated['lng']) ? (float)$validated['lng'] : null,
            city: $validated['city'] ?? null,
        );
    }
}
