<?php

namespace App\Domain\LiveStreams\DTOs;

use App\Domain\LiveStreams\Enums\LiveStreamVisibility;
use Illuminate\Http\UploadedFile;

readonly class CreateLiveStreamData
{
    public function __construct(
        public string $title,
        public string $category,
        public ?string $location,
        public LiveStreamVisibility $visibility,
        public ?float $subscriptionRate,
        public string $baseResolution,
        public string $outputResolution,
        public array $products = [],
        public ?UploadedFile $coverImage = null,
    ) {}

    public static function fromValidated(array $data, ?UploadedFile $coverImage = null): self
    {
        return new self(
            title: $data['title'],
            category: $data['category'],
            location: $data['location'] ?? null,
            visibility: LiveStreamVisibility::from($data['visibility']),
            subscriptionRate: $data['subscription_rate'] ?? null,
            baseResolution: $data['base_resolution'] ?? '1920x1080',
            outputResolution: $data['output_resolution'] ?? '1280x720',
            products: $data['products'] ?? [],
            coverImage: $coverImage,
        );
    }
}
