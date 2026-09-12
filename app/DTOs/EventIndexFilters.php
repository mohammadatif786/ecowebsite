<?php

namespace App\DTOs;

use App\Http\Requests\EventIndexRequest;

/**
 * Typed, immutable representation of the events-index filters.
 *
 * Built ONLY from a validated request, so by the time this object
 * exists, every property is already safe to use in a query.
 */
final class EventIndexFilters
{
    public function __construct(
        public readonly string $status = 'all',
        public readonly ?string $search = null,
        public readonly ?string $country = 'all',
    ) {}

    public static function fromRequest(EventIndexRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status: $validated['status'] ?? 'all',
            search: $validated['search'] ?? null,
            country: $validated['country'] ?? 'all',
        );
    }
}
