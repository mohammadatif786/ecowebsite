<?php

namespace App\DTOs;

use App\Http\Requests\EventFilterRequest;

final class EventFilterCriteria
{
    public function __construct(
        public readonly string $status = 'all',
        public readonly ?string $search = null,
        public readonly ?string $country = 'all',
        public readonly ?int $categoryId = null,
    ) {}

    public static function fromRequest(EventFilterRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status: $validated['status'] ?? 'all',
            search: $validated['search'] ?? null,
            country: $validated['country'] ?? 'all',
            categoryId: $validated['category_id'] ?? null,
        );
    }
}
