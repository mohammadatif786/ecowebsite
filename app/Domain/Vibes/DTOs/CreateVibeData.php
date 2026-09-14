<?php

namespace App\Domain\Vibes\DTOs;

use App\Domain\Vibes\Enums\VibePublisherType;
use App\Domain\Vibes\Enums\VibeVisibility;
use App\Http\Requests\Vibes\StoreVibeRequest;

final readonly class CreateVibeData
{
    public function __construct(
        public VibePublisherType $publisherType,
        public int $publisherId,
        public ?string $caption,
        public ?string $locationName,
        public ?string $locationPlaceId,
        public ?float $latitude,
        public ?float $longitude,
        public bool $allowCoinGifts,
        public VibeVisibility $visibility,
        public array $media,
        public array $mediaSources,
        public array $productIds,
        public array $eventIds,
    ) {}

    public static function fromRequest(StoreVibeRequest $request): self
    {
        $data = $request->validated();

        return new self(
            VibePublisherType::from($data['publisher_type']),
            (int) $data['publisher_id'],
            isset($data['caption']) ? trim($data['caption']) : null,
            $data['location_name'] ?? null,
            $data['location_place_id'] ?? null,
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null,
            (bool) ($data['allow_coin_gifts'] ?? true),
            VibeVisibility::from($data['visibility'] ?? VibeVisibility::Public->value),
            $request->file('media', []),
            $data['media_sources'] ?? [],
            array_map('intval', $data['product_ids'] ?? []),
            array_map('intval', $data['event_ids'] ?? []),
        );
    }
}
